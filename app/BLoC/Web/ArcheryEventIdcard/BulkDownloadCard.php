<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Models\ArcheryEvent;
use App\Models\ParticipantMemberTeam;
use App\Models\ArcheryEventIdcardTemplate;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryClub;
use App\Models\User;
use App\Models\ArcheryEventParticipantNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BulkDownloadCard extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $category_id = $parameters->get('event_category_id');
        $participants = ArcheryEventParticipant::where("event_category_id",$category_id)->where("status",1)->get();
        $archery_event = ArcheryEvent::find($parameters->get('event_id'));
        if(!$archery_event) throw new BLoCException("tidak ada data tersedia");

        $final_doc=[];
        
        $category = ArcheryEventCategoryDetail::find($category_id);
        $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);
        
        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $parameters->get('event_id'))->first();
        if(!$idcard_event) throw new BLoCException("Template event id card tidak ditemukan");

        foreach ($participants as $participant) {
            $member = ArcheryEventParticipantMember::where("archery_event_participant_id",$participant->id)->first();            
            $user = User::find($member->user_id);            
            $prefix = ArcheryEventParticipantNumber::getNumber($participant->id);
            
            $club = ArcheryClub::find($member->club_id);
            if(!$club){
                $club='';
            }else{
                $club=$club->name;
            }

            $html_template = base64_decode($idcard_event->html_template);
        
            if(!$idcard_event->background){
                $background='';
            }else{
                $background='background:url("'.$idcard_event->background.'")';
            }
            if(!$idcard_event->logo_event){
                $logo='<div id="logo" style="padding:3px"></div>';
            }else{
                $logo='<img src="'.$idcard_event->logo_event.'" alt="Avatar" style="float:left;width:40px">';
            }
            $avatar = !empty($user->avatar) ? $user->avatar : "https://i0.wp.com/eikongroup.co.uk/wp-content/uploads/2017/04/Blank-avatar.png?ssl=1";
            //dd($background);
            $final_doc[] = str_replace(
                ['{%member_name%}','{%avatar%}' ,'{%event_category%}','{%club%}',"background:url('')",'<div></div>'], 
                [$user->name, $avatar, $categoryLabel,$club,$background,$logo],
                $html_template
            );                    
        }

        $file_name = "idcard_".$categoryLabel.".pdf";
       
        $generate_idcard = PdfLibrary::setArrayDoc($final_doc)->setFileName($file_name)->generateIdcard();
            return [
                "file_name" => $file_name,
                "file_base_64" => $generate_idcard,
            ];

        
    }
    
}