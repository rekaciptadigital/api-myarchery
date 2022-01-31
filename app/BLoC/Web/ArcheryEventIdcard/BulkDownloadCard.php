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

        $archery_event_participants = ArcheryEventParticipant::where('event_id', $archery_event->id)->where('status', 1)->get();
        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $archery_event->id)->first();
        if(!$idcard_event) throw new BLoCException("Event ID Card tidak ditemukan");
        $html_template = base64_decode($idcard_event->html_template);

        foreach ($archery_event_participants as $archery_event_participant) {
            $archery_event_participant_member = ArcheryEventParticipantMember::where('archery_event_participant_id', $archery_event_participant->id)->first();

            $idcard_category = ArcheryEventIdcardTemplate::getCategoryLabel($archery_event_participant->id, $archery_event_participant->user_id);
            if($idcard_category == "") throw new BLoCException("Kategori tidak ditemukan");

            $prefix = ArcheryEventIdcardTemplate::setPrefix($archery_event_participant->id, $archery_event->id);
            if($prefix == "") throw new BLoCException("Prefix gagal digenerate");
    
            $member_id = ArcheryEventParticipantMemberNumber::getMemberNumber($archery_event->id, $archery_event_participant->user_id);
            $final_doc = str_replace(
                            ['{%member_name%}', '{%member_id%}', '{%event_name%}', '{%event_category%}'], 
                            [$archery_event_participant_member->name, $member_id, $archery_event->event_name, $idcard_category],
                            $html_template
                        );
            $file_name = "idcard_".$member_id.".pdf";
            $generate_idcard = PdfLibrary::setFinalDoc($final_doc)->setFileName($file_name)->generateIdcard();

            $idcard[] = [
                "file_name" => $file_name,
                "file_base_64" => $generate_idcard,
            ];
        }

        return $idcard;
    }
}