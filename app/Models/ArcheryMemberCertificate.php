<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCertificateTemplates;
use App\Libraries\PdfLibrary;
use App\Models\User;
use App\Models\ArcheryEventParticipantMember;

class ArcheryMemberCertificate extends Model
{
    protected $fillable = [
        'id',
        'member_id',
        'certificate_template_id',
       ];

    protected $replace_item_by_certificate_type = [
                "{%member_name%}" => "",
                "{%category_name%}" => "",
                "{%qualification_ranked%}" => "",
                "{%ranked%}" => "",
                "{%certificate_verify_url%}" => "",
                "{%background%}" => ""
    ];

    protected function prepareUserCertificate($event_id, $user_id){
        $certificate_templates = ArcheryEventCertificateTemplates::where("event_id",$event_id)->get();
        $certificates = [];
        $user_certificates = [];
        $user_certificate_by_categories = [];
        
        $members = ArcheryEventParticipantMember::select("archery_event_participant_members.id",
                                                        "archery_event_participants.event_category_id",
                                                        "archery_event_participants.club_id",
                                                        "archery_event_participants.competition_category_id",
                                                        "archery_event_participants.distance_id",
                                                        "archery_event_participants.age_category_id"
                                                        )->join("archery_event_participants","archery_event_participant_members.archery_event_participant_id","=","archery_event_participants.id")
                    ->where("archery_event_participants.event_id",$event_id)
                    ->where("archery_event_participants.type","individual")
                    ->where("archery_event_participant_members.user_id",$user_id)
                    ->where("archery_event_participants.status",1)
                    ->get();
        $user = User::find($user_id);

        $item = $this->replace_item_by_certificate_type;

        $item["{%member_name%}"] = strtoupper($user->name);
        foreach ($members as $key => $value) {
            $category = ArcheryEventCategoryDetail::getCategoryLabelComplete($value->event_category_id);
            $category_detail = ArcheryEventCategoryDetail::find($value->event_category_id);
            $item["{%category_name%}"] = $category;
            $elimination_member = ArcheryEventEliminationMember::where("member_id",$value->id)->first();
            
            foreach ($certificate_templates as $c => $template) {
                $type_certificate = $template->type_certificate;
                $type_certificate_label = ArcheryEventCertificateTemplates::getCertificateLabelByType($type_certificate);
                if($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("participant")){
                    
                }elseif($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("winner")){
                    if(!$elimination_member || $elimination_member->elimination_ranked > 3) continue;
                    $item["{%ranked%}"] = $elimination_member->elimination_ranked;
                }elseif($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("elimination")){
                    if(!$elimination_member) continue;
                }elseif($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("qualification_winner")){
                    if(!$elimination_member || $elimination_member->position_qualification > 3) continue;
                    $item["{%ranked%}"] = $elimination_member->position_qualification;
                }elseif($type_certificate == ArcheryEventCertificateTemplates::getCertificateType("team_qualification_winner") || $type_certificate == ArcheryEventCertificateTemplates::getCertificateType("mix_team_qualification_winner")){
                    if($value->club_id == 0) continue;
                    $team_participant = ArcheryEventParticipant::select("archery_event_participants.event_category_id")
                        ->where("archery_event_participants.type","team")
                        ->where("archery_event_participants.event_id",$event_id)
                        ->where("archery_event_participants.competition_category_id",$value->competition_category_id)
                        ->where("archery_event_participants.distance_id",$value->distance_id)
                        ->where("archery_event_participants.age_category_id",$value->age_category_id)
                        ->where("archery_event_participants.club_id",$value->club_id)
                        ->where("archery_event_participants.status",1)
                        ->groupBy("archery_event_participants.event_category_id")
                        ->get();
                    foreach ($team_participant as $tp => $team) {
                        $team_category_detail = ArcheryEventCategoryDetail::find($team->event_category_id);
                        if($team_category_detail->qualification_mode == "best_of_three"){
                            if($team_category_detail->team_category_id == "mix_team"){

                            }else{
                                $team_category = $team_category_detail->team_category_id == "individu female" ? "female_team" : "male_team";
                                if($team_category_detail->team_category_id == $team_category){
                                    
                                }
                            }
                        }
                    }
                }else{
                    continue;
                }
                
                $member_certificate_id = $value->id."-".$template->id;
                $validate_link = env("WEB_URL")."/certificate/validate/".$member_certificate_id;
                $item["{%certificate_verify_url%}"] = $validate_link;
                $item["{%background%}"] = $template->background_url;
                
                $user_certificates[] = [
                    "item" => $item,
                    "category" => $category,
                    "template" => $template->html_template,
                    "type" => $type_certificate,
                    "type_label" => $type_certificate_label
                ];
            };
            $files = [];

            foreach ($user_certificates as $uc => $user_certificate) {
                $html_template = base64_decode($user_certificate["template"]);
                foreach ($user_certificate["item"] as $i => $item_detail) {
                    $html_template = str_replace($i, $item_detail,$html_template);
                }

                $member_certificate = $this->firstOrNew(array(
                    'id' => $member_certificate_id,
                    'member_id' => $value->id,
                    'certificate_template_id' => $template->id,
                ));

                $path = "asset/certificate/user_".$user_id;
                if (!file_exists(public_path()."/".$path)) {
                    mkdir(public_path()."/".$path, 0775);
                }
                $path = "asset/certificate/user_".$user_id."/".$event_id;
                if (!file_exists(public_path()."/".$path)) {
                    mkdir(public_path()."/".$path, 0775);
                }
                $path = "asset/certificate/user_".$user_id."/".$event_id."/".$user_certificate["type"];
                if (!file_exists(public_path()."/".$path)) {
                    mkdir(public_path()."/".$path, 0775);
                }

                $file_name = $path."/"."[".$member_certificate_id."]".$category."-".$user_certificate["type_label"].".pdf";
                if (!file_exists(public_path()."/".$path)) {
                    PdfLibrary::setFinalDoc($html_template)->setFileName($file_name)->savePdf();
                }
                
                $files[] = [
                    "name" => $user_certificate["type_label"],
                    "url" =>  env('APP_HOSTNAME') . $file_name
                ];
            }
            if(!empty($files)){
                $user_certificate_by_categories[] = [
                    "category" => [
                        "id" => $value->event_category_id,
                        "name" => $category,
                    ],
                    "files" => $files
                ];
            }
        }
        
        return $user_certificate_by_categories;
    }
}