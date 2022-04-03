<?php

namespace App\BLoC\General\CategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\QandA;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetListCategoryByEventId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // parameter
        $event_id = $parameters->get("event_id");
        $type = $parameters->get("type");
        $competition_category_id = $parameters->get("competition_category_id");
        $age_category_id = $parameters->get("age_category_id");
        $distance_id = $parameters->get("distance_id");
        $team_category_id = $parameters->get("team_category_id");

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        $list_category_query = ArcheryEventCategoryDetail::select("archery_event_category_details.*")->where("event_id", $event_id);

        // filter by type
        $list_category_query->when($type, function ($query) use ($type) {
            return $query->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                ->where("archery_master_team_categories.type", $type);
        });

        // filter by competition
        $list_category_query->when($competition_category_id, function ($query) use ($competition_category_id) {
            return $query->join("archery_master_competition_categories", "archery_master_competition_categories.id", "=", "archery_event_category_details.competition_category_id")
                ->where("archery_master_competition_categories.id", $competition_category_id);
        });

        // filter by age
        $list_category_query->when($age_category_id, function ($query) use ($age_category_id) {
            return $query->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
                ->where("archery_master_age_categories.id", $age_category_id);
        });

        // filter by distance
        $list_category_query->when($distance_id, function ($query) use ($distance_id) {
            return $query->join("archery_master_distances", "archery_master_distances.id", "=", "archery_event_category_details.distance_id")
                ->where("archery_master_distances.id", $distance_id);
        });

        // filter by team
        $list_category_query->when($team_category_id, function ($query) use ($team_category_id) {
            return $query->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                ->where("archery_master_team_categories.id", $team_category_id);
        });

        $list_category_collection = $list_category_query->get();

        $output = [];
        $response = [];

        if ($list_category_collection->count() > 0) {
            foreach ($list_category_collection as $category) {
                $response["id"] = $category->id;
                $response["event_id"] = $category->event_id;
                $response["age_category_id"] = $category->age_category_id;
                $response["competition_category_id"] = $category->competition_category_id;
                $response["distance_id"] = $category->distance_id;
                $response["team_category_id"] = $category->team_category_id;
                $response["is_show"] = $category->is_show;
                $response["category_team"] = $category->category_team;
                $response["gender_category"] = $category->gender_category;
                $response["label_category"] = $category->label_category;
                $response["class_category"] = $category->class_category;
                $response["quota"] = $category->quota;
                $response["normal_fee"] = $category->fee;
                $response["early_bird_fee"] = $category->early_bird;
                $response["is_early_bird"] = $category->is_early_bird;
                $response["total_participant"] = ArcheryEventParticipant::getTotalPartisipantByEventByCategory($category->id);

                array_push($output, $response);
            }
        }
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "type" => "in:Individual,Team"
        ];
    }
}
