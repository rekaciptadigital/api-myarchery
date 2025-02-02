<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

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

        if ($admin->id != $event->admin_id) {
            throw new BLoCException("access denied");
        }

        $parent_classifification_id = $event->parent_classification;

        if ($parent_classifification_id == 0) {
            throw new BLoCException("parent calassification_id invalid");
        }

        $participant_query = ArcheryEventParticipant::select(
            'archery_event_participant_members.id as member_id',
            'archery_event_participants.id as participant_id',
            'archery_event_participants.user_id',
            'archery_event_participants.event_id',
            'archery_event_participants.event_category_id',
            'users.name',
            'users.email',
            "archery_event_participants.club_id",
            "archery_clubs.name as club_name",
            "archery_event_participants.classification_country_id as country_id",
            "countries.name as country_name",
            "archery_event_participants.classification_province_id as province_id",
            $event->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
            "archery_event_participants.city_id",
            $event->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
            "archery_event_participants.children_classification_id",
            "children_classification_members.title as children_classification_members_name",
            'archery_event_participants.phone_number',
            'archery_event_participants.competition_category_id',
            'archery_event_participants.status as status_participant',
            'transaction_logs.status as status_transaction_log',
            'transaction_logs.expired_time as expired_time'
        );

        // jika mewakili club
        $participant_query = $participant_query->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");


        // jika mewakili negara
        $participant_query = $participant_query->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");


        // jika mewakili provinsi
        if ($event->classification_country_id == 102) {
            $participant_query = $participant_query->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
        } else {
            $participant_query = $participant_query->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
        }


        // jika mewakili kota
        if ($event->classification_country_id == 102) {
            $participant_query = $participant_query->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
        } else {
            $participant_query = $participant_query->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
        }


        // jika berasal dari settingan admin
        $participant_query = $participant_query->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");



        $participant_query = $participant_query->join('archery_event_participant_members', 'archery_event_participant_members.archery_event_participant_id', '=', 'archery_event_participants.id')
            ->leftJoin('transaction_logs', 'transaction_logs.id', '=', 'archery_event_participants.transaction_log_id')
            ->join("users", "users.id", "=", "archery_event_participants.user_id")
            ->where("archery_event_participants.status", "!=", 6)
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
            $participant_collection = $participant_query->orderBy('archery_event_participants.id', 'DESC')->get();
            $data = [];
            foreach ($participant_collection as $pc) {
                $response = [];
                $status_payment = "";
                if (
                    $pc->status_participant == 2 ||
                    $pc->status_transaction_log == 2 ||
                    ($pc->status_participant == 4 && $pc->status_transaction_log == 4 && $pc->expired_time < time())
                ) {
                    $status_payment = "Expired";
                } elseif ($pc->status_participant == 4 && $pc->status_transaction_log == 4 && $pc->expired_time > time()) {
                    $status_payment = "Belum Lunas";
                } elseif ($pc->status_participant == 3 || $pc->status_transaction_log == 3) {
                    $status_payment = "Failed";
                } elseif ($pc->status_participant == 5) {
                    $status_payment = "Refund";
                } elseif ($pc->status_participant == 1) {
                    $status_payment = "Lunas";
                }
                $response["age_category_id"] = $pc->age_category_id;
                $response["member_id"] = $pc->member_id;
                $response["participant_id"] = $pc->participant_id;
                $response["user_id"] = $pc->user_id;
                $response["event_id"] = $pc->event_id;
                $response["event_category_id"] = $pc->event_category_id;
                $response["name"] = $pc->name;
                $response["email"] = $pc->email;
                $response["club_id"] = $pc->club_id;
                $response["club_name"] = $pc->club_name;
                $response["country_id"] = $pc->country_id;
                $response["country_name"] = $pc->country_name;
                $response["province_id"] = $pc->province_id;
                $response["province_name"] = $pc->province_name;
                $response["city_id"] = $pc->city_id;
                $response["city_name"] = $pc->city_name;
                $response["children_classification_id"] = $pc->children_classification_id;
                $response["children_classification_members_name"] = $pc->children_classification_members_name;
                $response["parent_classification_type"] = $parent_classifification_id;
                $response["phone_number"] = $pc->phone_number;
                $response["competition_category_id"] = $pc->competition_category_id;
                $response["status_payment"] = $status_payment;
                $data[] = $response;
            }

            return $data;
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
