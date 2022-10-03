<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class UpdateArcheryEventStatus extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get('id');
        $status = $parameters->get('status');


        $archery_event = ArcheryEvent::find($event_id);
        if ($archery_event->admin_id != $admin->id) {
            throw new BLoCException("You're not the owner of this event");
        }

        if ($status == 0) {
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
                throw new BLoCException("tidak dapat ubah status karena telah ada peserta yang mendaftar");
            }
        } else {
            $qualification_time = ArcheryEventQualificationTime::select("archery_event_qualification_time.*")
                ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_qualification_time.category_detail_id")
                ->join("archery_events", "archery_events.id", "=", "archery_event_category_details.event_id")
                ->where("event_id", $event_id)
                ->first();

            if (!$qualification_time) {
                throw new BLoCException("harap lengkapi data event sebelum publish");
            }
        }


        $archery_event->status = $status;
        $archery_event->save();

        return $archery_event;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer|exists:archery_events,id',
            'status' => "required|in:1,0"
        ];
    }
}
