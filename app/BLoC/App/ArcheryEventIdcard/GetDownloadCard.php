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
        $detail_member  = ArcheryEventParticipant::getMemberByUserId($user['id'], $participant_id);
        if(!$detail_member) throw new BLoCException("Anda tidak mengikuti event ini");

        $archery_event  = ArcheryEvent::select('id', 'event_name', 'admin_id')->where('id', $detail_member->event_id)->first();
        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $archery_event->id)->first();
        if(!$idcard_event) throw new BLoCException("Event ID Card tidak ditemukan");

        $idcard_category = ArcheryEventIdcardTemplate::getCategoryLabel($participant_id, $user['id']);
        if($idcard_category == "") throw new BLoCException("Kategori tidak ditemukan");

        $prefix = $this->generatePrefix($archery_event->admin_id, $archery_event->id);

        $archery_event_participant_member_number = ArcheryEventParticipantMemberNumber::where('prefix', $prefix)->where('participant_member_id', $participant_member_id)->first();
        $member_idcard  = ArcheryEventParticipantMemberNumber::firstOrNew(array(
                            'prefix' => $prefix,
                            'participant_member_id' => $participant_member_id,
                          ));
        $member_idcard->save();

        if(!$archery_event_participant_member_number) {
            $member_id = $member_idcard->prefix .''. $this->prefixFormatNumber($member_idcard->id);
        } else {
            $member_id = $archery_event_participant_member_number->prefix .''. $this->prefixFormatNumber($archery_event_participant_member_number->sequence);
        }

        $html_template = base64_decode($idcard_event->html_template);
        $final_doc = str_replace(
                        ['{%member_name%}', '{%member_id%}', '{%event_name%}', '{%event_category%}'], 
                        [$archery_event_participant_member->name, $member_id, $archery_event->event_name, $idcard_category],
                        $html_template
                    );
        $file_name = "idcard_".$member_id.".pdf";
        $generate_idcard = PdfLibrary::setFinalDoc($final_doc)->setFileName($file_name)->generateIdcard();

        return [
            "file_name" => $file_name,
            "file_base_64" => $generate_idcard,
        ];
    }

    private function generatePrefix($eo_id, $event_id)
    {
        $prefix = $this->prefixFormatNumber($eo_id) .''. $this->prefixFormatNumber($event_id);
        return $prefix;
    }

    private function prefixFormatNumber($number)
    {
        if ($number <= 9){
            $number = "00".$number;
        } else if ($number <= 99 && $number > 9 ){
            $number = "0".$number;
        } else {
            $number = "".$number;
        }
        return $number;
    }  
}