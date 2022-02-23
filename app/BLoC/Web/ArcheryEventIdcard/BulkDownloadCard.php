<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Models\ArcheryEvent;
use App\Models\ParticipantMemberTeam;
use App\Models\ArcheryEventIdcardTemplate;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use App\Models\ArcheryClub;
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
        $archery_event = ArcheryEvent::find($parameters->get('event_id'));

        if(!$archery_event) throw new BLoCException("Event tidak ditemukan");

        $find_participant_member_team = ParticipantMemberTeam::select('users.name','archery_event_participants.event_id','archery_event_participants.user_id','participant_member_teams.participant_id','archery_event_participants.club_id')
        ->where('participant_member_teams.type','individual')
        ->where('archery_events.id', $parameters->get('event_id'))
        ->leftJoin("archery_event_participants","archery_event_participants.id","participant_member_teams.participant_id")
        ->leftJoin("users","users.id","archery_event_participants.user_id")
        ->join("archery_events","archery_events.id","archery_event_participants.event_id")
        ->get();

        if($find_participant_member_team->isEmpty()) throw new BLoCException("Tidak ada partisipan tipe individu pada event ini");
        
        if($archery_event->admin_id != $admin->id) throw new BLoCException("You're not the owner of this event");
        
        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $parameters->get('event_id'))->first();
        if(!$idcard_event) throw new BLoCException("Template event id card tidak ditemukan");
        
        $final_doc=[];
        
        foreach ($find_participant_member_team as $participant_member) {
            $category = ArcheryEventIdcardTemplate::getCategoryLabel($participant_member->participant_id, $participant_member->user_id);
            if($category == "") throw new BLoCException("Kategori tidak ditemukan");
            
            $prefix = ArcheryEventIdcardTemplate::setPrefix($participant_member->participant_id, $participant_member->event_id);
            if($prefix == "") throw new BLoCException("Prefix gagal digenerate");
            
            $club = ArcheryClub::find($participant_member->club_id);
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
    
            //dd($background);
            $final_doc[] = str_replace(
                ['{%member_name%}', '{%event_category%}','{%club%}',"background:url('')",'<div></div>'], 
                [$participant_member->name, $category,$club,$background,$logo],
                $html_template
            );
                    
        }
        $file_name = "idcard_".$archery_event->name.".pdf";
       
        $generate_idcard = PdfLibrary::setArrayDoc($final_doc)->setFileName($file_name)->generateIdcard();

            return [
                "file_name" => $file_name,
                "file_base_64" => $generate_idcard,
            ];

        
    }
    
}