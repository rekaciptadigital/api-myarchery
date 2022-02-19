<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventIdcardTemplate;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
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
        $idcard = [];
        $admin = Auth::user();
        $archery_event = ArcheryEvent::find($parameters->get('event_id'));
        if(!$archery_event) throw new BLoCException("Data not found");
        if($archery_event->admin_id != $admin->id) throw new BLoCException("You're not the owner of this event");

        $archery_event_participants =ArcheryEventParticipantMember::select('archery_event_participant_members.*', 'archery_event_participants.event_id', 'archery_event_participants.event_category_id','archery_event_participants.club_id',DB::RAW('archery_event_participants.id as partisipant'))
        ->leftJoin('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
        ->where('archery_event_participants.status', 1)
        ->get();
        
        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $archery_event->id)->first();
        if(!$idcard_event) throw new BLoCException("Event ID Card tidak ditemukan");
        
        foreach ($archery_event_participants as $archery_event_participant) {
            $participant_id = $archery_event_participant->partisipant;
            $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $archery_event->id)->first();
            if(!$idcard_event) throw new BLoCException("Event ID Card tidak ditemukan");
            
            $idcard_category = ArcheryEventIdcardTemplate::getCategoryLabel($participant_id, $archery_event_participant->user_id);
            
            if($idcard_category==" ") throw new BLoCException("Kategori tidak ditemukan");
           
            $prefix = ArcheryEventIdcardTemplate::setPrefix($participant_id, $archery_event->id);
            if($prefix == " ") throw new BLoCException("Prefix gagal digenerate");
            
            $club_find = ArcheryClub::find($archery_event_participant->club_id);
            if(!$club_find){
                $club='';
            }else{
                $club=$club_find->name;
            }
            
            $member_id = ArcheryEventParticipantMemberNumber::getMemberNumber($archery_event->id, $archery_event_participant->user_id);
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
            
            $final_doc[] = str_replace(
            ['{%member_name%}', '{%event_category%}','{%club%}',"background:url('')",'<div></div>'], 
            [$archery_event_participant->name, $idcard_category,$club,$background,$logo],
            $html_template
            );
                    
        }
        $file_name = "idcard_".$parameters->get('event_id').".pdf";
       
        $generate_idcard = PdfLibrary::setArrayDoc($final_doc)->setFileName($file_name)->generateIdcard();

            return [
                "file_name" => $file_name,
                "file_base_64" => $generate_idcard,
            ];

        
    }
    
}