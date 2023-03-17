<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\OrderEvent;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class DetailEventOrder extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $order_event_id = $parameters->get("order_event_id");
        $order_event = OrderEvent::find($order_event_id);
        if ($order_event->user_id != $user->id) {
            throw new BLoCException("forbiden");
        }

        $archery_event = ArcheryEvent::find($order_event->event_id);
        if (!$archery_event) {
            throw new BLoCException("event not found");
        }

        $members = User::select("users.*", "archery_clubs.name as club_name", "archery_event_participants.event_category_id")
            ->join("archery_event_participants", "archery_event_participants.user_id", "=", "users.id")
            ->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
            ->where("archery_event_participants.order_event_id", $order_event->id)
            ->get();


        $list_member = [];
        $detail_category = null;

        if (count($members) > 0) {
            $category = ArcheryEventCategoryDetail::select(
                "archery_event_category_details.id",
                "archery_master_competition_categories.label as competition",
                "archery_master_age_categories.label as age",
                "archery_master_distances.label as distance",
                "archery_master_team_categories.label as team"
            )
                ->join("archery_master_competition_categories", "archery_master_competition_categories.id", "=", "archery_event_category_details.competition_category_id")
                ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
                ->join("archery_master_distances", "archery_master_distances.id", "=", "archery_event_category_details.distance_id")
                ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                ->where("archery_event_category_details.id", $members[0]->event_category_id)
                ->first();
            if ($category) {
                $detail_category = [
                    "id" => $category->id,
                    "age" => $category->age,
                    "competition" => $category->competition,
                    "distance" => $category->distance,
                    "team" => $category->team
                ];
            }
            foreach ($members as $key => $m) {
                $list_member[] = [
                    "name" => $user->name,
                    "club_name" => $m->club_name,
                    "age" => $m->age,
                    "photo" => $user->avatar
                ];
            }
        }
        $transaction_info = PaymentGateWay::transactionLogPaymentInfo($order_event->transaction_log_id);

        $output = [
            "order_event_id" => $order_event->id,
            "total_price" => $order_event->total_price,
            "user_order" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "phone_number" => $user->phone_number
            ],
            "archery_event" => [
                "id" => $archery_event->id,
                "event_name" => $archery_event->event_name,
                "poster" => $archery_event->poster,
                "event_start" => $archery_event->event_start_datetime,
                "event_end" => $archery_event->event_end_datetime,
                "location" => $archery_event->location,
                "event_slug" => $archery_event->event_slug
            ],
            "list_member" => $list_member,
            "transaction_info" => $transaction_info,
            "category" => $detail_category,
        ];
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "order_event_id" => "required|exists:order_events,id",
        ];
    }
}
