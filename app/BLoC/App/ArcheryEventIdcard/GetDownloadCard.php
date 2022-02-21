<?php

namespace App\BLoC\App\ArcheryEventIdcard;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventIdcardTemplate;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryClub;
use Illuminate\Support\Facades\DB;

class GetDownloadCard extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant_member_id = $parameters->get('participant_member_id');
        $user = Auth::guard('app-api')->user();

        $archery_event_participant_member = ArcheryEventParticipantMember::find($participant_member_id);
        if(!$archery_event_participant_member) throw new BLoCException("Data tidak ditemukan");

        $participant_id = $archery_event_participant_member->archery_event_participant_id;
        
        $detail_member  = ArcheryEventParticipantMember::select('archery_event_participant_members.*', 'archery_event_participants.event_id', 'archery_event_participants.event_category_id','archery_event_participants.club_id',DB::RAW('archery_event_participants.id as partisipant'))
        ->leftJoin('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
        ->where('archery_event_participants.status', 1)
        ->where('archery_event_participant_members.id', $participant_member_id)
        ->where('archery_event_participant_members.user_id', $user['id'])
        ->get();

        if(!$detail_member) throw new BLoCException("Anda tidak mengikuti event ini");

        $archery_event  = ArcheryEvent::select('id', 'event_name', 'admin_id')->where('id', $detail_member->event_id)->first();
        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $archery_event->id)->first();
        if(!$idcard_event) throw new BLoCException("Event ID Card tidak ditemukan");

        $idcard_category = ArcheryEventIdcardTemplate::getCategoryLabel($participant_id, $user['id']);
        if($idcard_category == "") throw new BLoCException("Kategori tidak ditemukan");
        
        $prefix = ArcheryEventIdcardTemplate::setPrefix($participant_id, $archery_event->id);
        if($prefix == "") throw new BLoCException("Prefix gagal digenerate");
        
        $club_find = ArcheryClub::find($detail_member->club_id);
        if(!$club_find){
            $club='';
        }else{
            $club=$club_find->name;
        }

        $member_id = ArcheryEventParticipantMemberNumber::getMemberNumber($archery_event->id, $user['id']);
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
        $final_doc = str_replace(
            ['{%member_name%}', '{%event_category%}','{%club%}',"background:url('')",'<div></div>'], 
            [$archery_event_participant_member->name, $idcard_category,$club,$background,$logo],
            $html_template
        );
                   
        $file_name = "idcard_".$participant_member_id.".pdf";
       
        $generate_idcard = PdfLibrary::setFinalDoc($final_doc)->setFileName($file_name)->generateIdcard();

            return [
                "file_name" => $file_name,
                "file_base_64" => $generate_idcard,
            ];
    }
}