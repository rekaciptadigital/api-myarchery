<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class ListMemberTeamV2 extends Retrieval
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
        $competition_category_id = $parameters->get("competition_category_id");
        $distance_id = $parameters->get("distance_id");
        $team_category_id = $parameters->get("team_category_id");
        $age_category_id = $parameters->get("age_category_id");
        $name = $parameters->get("name");
        $status = $parameters->get("status");

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("Event tidak tersedia");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("access denied");
        }

        $participant_query = ArcheryEventParticipant::select("archery_event_participants.*", "transaction_logs.status as status_payment", "transaction_logs.expired_time")
            ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where("archery_event_participants.event_id", $event_id)
            ->where("archery_event_participants.type", "team")
            ->where("archery_event_participants.status", "!=", 6);

        // filter by name
        $participant_query->when($name, function ($query) use ($name) {
            return $query->whereRaw("archery_event_participants.name LIKE ?", ["%" . $name . "%"]);
        });

        // filter by competition_id
        $participant_query->when($competition_category_id, function ($query) use ($competition_category_id) {
            return $query->where("competition_category_id", $competition_category_id);
        });

        // filter by distance_id
        $participant_query->when($distance_id, function ($query) use ($distance_id) {
            return $query->where("distance_id", $distance_id);
        });

        // filter by team_category_id
        $participant_query->when($team_category_id, function ($query) use ($team_category_id) {
            return $query->where("team_category_id", $team_category_id);
        });

        // filter by age_category_id
        $participant_query->when($age_category_id, function ($query) use ($age_category_id) {
            return $query->where("age_category_id", $age_category_id);
        });

        $participant_query->when($status, function ($query) use ($status) {
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

        $participant_collection = $participant_query->orderBy('id', 'DESC')->limit($limit)->offset($offset)->get();



        $detail_member = [];

        foreach ($participant_collection as $participant) {

            $club = ArcheryClub::find($participant->club_id);
            $club_name = $club ? $club->name : "";

            $status_payment = "";
            if ($participant->status_payment != null) {
                if ($participant->status == 1) {
                    $status_payment = "Lunas";
                    $order_payment = 1;
                }

                if ($participant->status == 4 && $participant->expired_time >= time() && $participant->status_payment == 4) {
                    $status_payment = "Belum Lunas";
                    $order_payment = 2;
                }

                if (($participant->status == 2) || ($participant->status_payment == 4 && $participant->expired_time <= time())) {
                    $status_payment = "Expired";
                    $order_payment = 3;
                }

                if ($participant->status == 5) {
                    $status_payment = "Refund";
                    $order_payment = 5;
                }
            } else {
                $status_payment = "Gratis";
                $order_payment = 4;
            }

            $detail_member[] = [
                "participant_id" => $participant->id,
                "user_id" => $participant->user_id,
                "name" => $participant->name,
                "email" => $participant->email,
                "club_name" => $club_name,
                "phone_number" => $participant->phone_number,
                "competition_category" => $participant->competition_category_id,
                "status_payment" => $status_payment,
                "age_category" => $participant->age_category_id,
                "order_payment" => $order_payment
            ];
        }

        $order_payment = array_column($detail_member, 'order_payment');
        array_multisort($order_payment, SORT_ASC, $detail_member);

        return $detail_member;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
            'status' => "in:1,2,3,4,5"
        ];
    }
}
