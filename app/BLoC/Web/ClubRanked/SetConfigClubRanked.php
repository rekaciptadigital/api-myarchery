<?php

namespace App\BLoC\Web\ClubRanked;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\GroupCategory;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class SetConfigClubRanked extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event = ArcheryEvent::find($parameters->get('event_id'));
        $rating_flag = $parameters->get("rating_flag");
        $categories = $parameters->get("categories");
        $rules_rating_club = $parameters->get("rules_rating_club");
        $group_name = $parameters->get("group_name");
        if (!$event) {
            throw new BLoCException('event not found');
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $categoriy_detail = ArcheryEventCategoryDetail::where("event_id", $event->id)->get();
        foreach ($categoriy_detail as $c) {
            $c->rating_flag = 1;
            $c->rules_rating_club = 1;
            if ($c->group_category_id != 0) {
                $group_category = GroupCategory::find($c->group_category_id);
                if ($group_category) {
                    $group_category->delete();
                }
            }
            $c->group_category_id = 0;
            $c->save();
        }
        $response = [];


        if ($rating_flag == 2 || $rating_flag == 3) {
            $response["type"] = $rating_flag;
            $list_categories = [];
            $response_category = [];
            $group_category_id = 0;
            if ($rating_flag == 3) {
                $group_category = new GroupCategory;
                $group_category->group_category_name = $group_name;
                $group_category->event_id = $event->id;
                $group_category->save();
                $response["group_name"] = $group_name;
                $group_category_id = $group_category->id;
            }
            foreach ($categories as $c) {
                foreach ($categoriy_detail as $cd) {
                    if (
                        $cd->competition_category_id == $c["competition_category_id"]
                        && $cd->age_category_id == $c["age_category_id"]
                        && $cd->distance_id == $c["distance_id"]
                    ) {
                        $cd->rating_flag = $rating_flag;
                        $cd->rules_rating_club = $rules_rating_club;
                        $cd->group_category_id = $group_category_id;
                        $cd->save();

                        $response["rules_rating_club"] = $rules_rating_club;

                        $response_category["id"] = $cd->id;
                        $response_category["event_id"] = $cd->event_id;
                        $response_category["competition_category_id"] = $cd->competition_category_id;
                        $response_category["age_category_id"] = $cd->age_category_id;
                        $response_category["distance_id"] = $cd->distance_id;
                        $response_category["team_category_id"] = $cd->team_category_id;
                        $response_category["rules_rating_club"] = $cd->rules_rating_club;
                        $response_category["rating_flag"] = $cd->rating_flag;
                        $response_category["group_category_id"] = $cd->group_category_id;
                        $list_categories[] = $response_category;
                    }
                }
            }

            $response["list_category"] = $list_categories;
        }

        return $response;
    }

    protected function validation($parameters)
    {
        $rules = [
            "event_id" => "required",
            "rating_flag" => "required:in:1,2,3"
        ];

        if ($parameters->get("rating_flag") == 2 || $parameters->get("rating_flag") == 3) {
            $rules["categories"] = "required|array";
            $rules["categories.*.competition_category_id"] = "required";
            $rules["categories.*.age_category_id"] = "required";
            $rules["categories.*.distance_id"] = "required";
            $rules["rules_rating_club"] = "required|in:1,2";
        }

        if ($parameters->get("rating_flag") == 3) {
            $rules["group_name"] = "required|string";
        }

        return $rules;
    }

    private function setNormalRules($event_id)
    {
        $categories = ArcheryEventCategoryDetail::where("event_id", $event_id)->get();
        foreach ($categories as $c) {
            $c->rating_flag = 1;
            $c->rules_rating_club = 1;
            if ($c->group_category_id != 0) {
                $group_category = GroupCategory::find($c->group_category_id);
                if ($group_category) {
                    $group_category->delete();
                }
            }
            $c->group_category_id = 0;
            $c->save();
        }
    }
}
