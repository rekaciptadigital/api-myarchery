<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\TransactionLog;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListMemberV2 extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page') ? $parameters->get('page') : 1;
        $offset = ($page - 1) * $limit;
        $event_id = $parameters->get("event_id");
        $event_category_id = $parameters->get("event_category_id");
        $is_pagination = $parameters->get("is_pagination") ? $parameters->get("is_pagination") : 0;
        $status = $parameters->get("status");

        $perPage = 7;

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("Event tidak tersedia");
        }

        $participant_query = ArcheryEventParticipant::select(
            'archery_event_participant_members.id as member_id',
            'archery_event_participants.id as participant_id',
            'archery_event_participants.user_id',
            'archery_event_participants.event_id',
            'archery_event_participants.event_category_id',
            'users.name',
            'users.email',
            'archery_clubs.name as club_name',
            'archery_event_participants.phone_number',
            'archery_event_participants.competition_category_id',
            DB::RAW('case when archery_event_participants.status=1 then "Lunas" when transaction_logs.status=4 and transaction_logs.expired_time>= now() then "Belum Lunas" when (transaction_logs.status=4 or transaction_logs.status=2) and transaction_logs.expired_time<= now() then "Expired" when archery_event_participants.status=5 then "Refund" else "Gratis" END AS status_payment'),
            'archery_event_participants.age_category_id',
            DB::RAW('case when transaction_logs.status=1 then 1 when transaction_logs.status=4 and transaction_logs.expired_time>= now() then 2 when (transaction_logs.status=4 or transaction_logs.status=2) and transaction_logs.expired_time<= now() then 3 when archery_event_participants.status=5 then 5 else 4 END AS order_payment')
        )->leftJoin('archery_event_participant_members', 'archery_event_participant_members.archery_event_participant_id', '=', 'archery_event_participants.id')
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_participants.club_id')
            ->leftJoin('transaction_logs', 'transaction_logs.id', '=', 'archery_event_participants.transaction_log_id')
            ->join("users", "users.id", "=", "archery_event_participants.user_id")
            ->where("archery_event_participants.event_id", $event_id)
            ->where("archery_event_participants.status", "!=", 6)
            ->where("archery_event_participants.type", "individual");


        // filter by event_category_id
        $participant_query->when($event_category_id, function ($query, $event_category_id) {
            return $query->where("archery_event_participants.event_category_id", $event_category_id);
        });

        $participant_query->when($status, function ($query, $status) {
            if ($status == 4) {
                return $query->where("archery_event_participants.status", 4)->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", ">", time());
            } elseif ($status == 2) {
                return $query->where(function ($qr) {
                    return $qr->where("archery_event_participants.status", 2)->orWhere(function ($q) {
                        return $q->where("archery_event_participants.status", 4)->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", "<", time());
                    });
                });
            } else {
                return $query->where("archery_event_participants.status", $status);
            }
        });

        if ($is_pagination == 1) { //get all data without pagination
            return $participant_query->orderBy('archery_event_participants.id', 'DESC')->get();
        }

        $data = $this->paginate($participant_query->orderBy('archery_event_participants.id', 'DESC')->paginate($perPage));
        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
            'status' => "in:1,2,3,4,5"
        ];
    }
    protected function paginate($models)
    {
        $data = $models;
        foreach ($data as $key => $val) {
            $number = ($data->currentpage() - 1) * $data->perpage() + $key + 1;
            $val->No = $number;
        }
        return $data;
    }
}
