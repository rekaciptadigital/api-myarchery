<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class DeleteEvent extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get("event_id");

        $event = ArcheryEvent::find($event_id);

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $count_user_join_or_order_event = ArcheryEventParticipant::select("archery_event_participants.*")
            ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where("archery_event_participants.event_id", $event_id)
            ->where(function ($query) {
                $query->where("archery_event_participants.status", 1)
                    ->orWhere(function ($q) {
                        $q->where("archery_event_participants.status", 4)
                            ->where("transaction_logs.status", 4)
                            ->where("transaction_logs.expired_time", ">", time());
                    });
            })->get()
            ->count();

        if ($count_user_join_or_order_event > 0) {
            throw new BLoCException("tidak dapat hapus event telah ada peserta yang mendaftar");
        }

        // delete category
        $categories = ArcheryEventCategoryDetail::where("event_id", $event_id)->get();

        foreach ($categories as $c) {
            $participants = ArcheryEventParticipant::where("event_category_id", $c->id)->get();
            foreach ($participants as $p) {
                $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $p->id)->first();
                if ($member) {
                    $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id")->first();
                    if ($schedule) {
                        $schedule->delete();
                    }
                    $member->delete();
                }
                $p->delete();
            }
            $c->delete();
        }

        $event->delete();
        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer|exists:archery_events,id"
        ];
    }
}
