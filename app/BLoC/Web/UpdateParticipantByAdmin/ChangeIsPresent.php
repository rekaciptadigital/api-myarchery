<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class ChangeIsPresent extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get("event_id");
        $participant_id = $parameters->get("participant_id");
        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak tersedia");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $participant = ArcheryEventParticipant::where("id", $participant_id)->where("event_id", $event_id)->first();
        if (!$participant) {
            throw new BLoCException("participant tidak ditemukan");
        }

        // $event_elimination = ArcheryEventElimination::where("event_category_id", $participant->event_category_id)->first();
        // if ($event_elimination) {
        //     throw new BLoCException("proses ditolak karena jumlah peserta eliminasi telah ditentukan");
        // }

        $participant->update([
            "is_present" => $parameters->get("is_present")
        ]);

        if ($participant->is_present === 0) {
            $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
            if (!$member) {
                throw new BLoCException("member nan");
            }

            if ($member->have_shoot_off != 0) {
                $member->update([
                    "have_shoot_off" => 0
                ]);
            }
        }

        return $participant;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
            "participant_id" => "required",
            "is_present" => "required|in:0,1"
        ];
    }
}
