<?php

namespace App\BLoC\Web\ArcheryEventParticipant;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Retrieval;

class GetArcheryEventParticipantMember extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_id = $parameters->get('category_id');
        $status = $parameters->get('status');

        $count = [
            "pending" => ArcheryEventParticipant::getTotalPartisipantEventByStatus($category_id, 4),
            "expired" => ArcheryEventParticipant::getTotalPartisipantEventByStatus($category_id, 2),
            "success" => ArcheryEventParticipant::getTotalPartisipantEventByStatus($category_id, 1),
            "all" => ArcheryEventParticipant::getTotalPartisipantEventByStatus($category_id, 1),
        ];



        // $participant = ArcheryEventParticipant::select("archery_event_participants.*", "transaction_logs.order_id", "archery_event_participants.status", "transaction_logs.expired_time")
        //     ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
        //     ->where('archery_event_participants.event_category_id', $category_id)
        //     ->where(function ($query) use ($status) {
        //         if (!is_null($status) && $status != 0) {
        //             $query->where('archery_event_participants.status', $status);
        //             if ($status == 2) {
        //                 $query->orWhere(function ($query) use ($status) {
        //                     $query->where("transaction_logs.status", 4);
        //                     $query->where("transaction_logs.expired_time", "<=", time());
        //                 });
        //             }
        //             if ($status == 1) {
        //                 $query->orWhere(function ($query) use ($status) {
        //                     $query->where("archery_event_participants.status", 1);
        //                 });
        //             }
        //             if ($status == 4) {
        //                 $query->where("transaction_logs.expired_time", ">=", time());
        //             }
        //         }
        //     })
        //     ->orderBy('archery_event_participants.created_at', 'DESC')->get();

        $participant_query = ArcheryEventParticipant::select("archery_event_participants.*", "transaction_logs.order_id", "archery_event_participants.status", "transaction_logs.expired_time")
            ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where('archery_event_participants.event_category_id', $category_id);

        $participant_query->when($status, function ($query) use ($status) {
            if ($status == 2) {
                return $query->where('transaction_logs.status', 4)->where('transaction_logs.expired_time', '<=', time());
            }

            if ($status == 4) {
                return $query->where('transaction_logs.status', 4)->where('transaction_logs.expired_time', '>=', time());
            }

            if ($status == 1) {
                return $query->where('archery_event_participants.status', 1);
            }
        });

        $participant_collect = $participant_query->orderBy('archery_event_participants.created_at', 'DESC')->get();

        $participant_ids = [];
        $participants = [];
        foreach ($participant_collect as $key => $value) {
            $participants[$value->id] = $value;
            $participant_ids[] = $value->id;
        }

        $members = [];
        if ($participant_collect)
            $members = ArcheryEventParticipantMember::whereIn("archery_event_participant_id", $participant_ids)->get();

        $list = [];
        foreach ($members as $k => $v) {
            if (!isset($participants[$v->archery_event_participant_id])) {
                continue;
            }
            $p = $participants[$v->archery_event_participant_id];
            $p->member = $v;
            if ($p->expired_time <= time() && $p->status == 4) {
                $p->status = 2;
            }
            $p->status_label = TransactionLog::getStatus($p->status);
            $list[] = $p;
        }

        return ["list" => $list, "count" => $count];
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_events,id',
        ];
    }
}
