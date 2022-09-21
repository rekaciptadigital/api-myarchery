<?php

namespace App\BLoC\Web\ClubRanked;

use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\GroupCategory;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetConfigClubRanked extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$event) {
            throw new BLoCException('event not found');
        }

        if ($event->admin_id != $admin->id) {
            $role = AdminRole::where("admin_id", $admin->id)->where("event_id", $event->id)->first();
            if (!$role || $role->role_id != 6) {
                throw new BLoCException("you are not owner this event");
            }
        }

        $categoriy_detail = ArcheryEventCategoryDetail::where("event_id", $event->id)->get();

        $response = [];

        $list_categories = [];
        foreach ($categoriy_detail as $cd) {
            $response_category = [];
            $is_exist = false;
            if ($cd->rating_flag == 2 || $cd->rating_flag == 3) {
                $response["type"] = $cd->rating_flag;
                $response["rules_rating_club"] = $cd->rules_rating_club;
                $response["label_rules_rating_club"] = $cd->rules_rating_club == 1 ? "digabung" : "dipisah";
                if ($cd->rating_flag == 3) {
                    $group_category = GroupCategory::find($cd->group_category_id);
                    if ($group_category) {
                        $response["group_category_name"] = $group_category->group_category_name;
                    }
                }
                foreach ($list_categories as $lc) {
                    if (
                        isset($lc["competition_category_id"]) && $lc["competition_category_id"] == $cd->competition_category_id
                        && isset($lc["age_category_id"]) && $lc["age_category_id"] == $cd->age_category_id
                        && isset($lc["distance_id"]) && $lc["distance_id"] == $cd->distance_id
                    ) {
                        $is_exist = true;
                    }
                }
                if ($is_exist == true) {
                    continue;
                }
                $age_category = ArcheryMasterAgeCategory::find($cd->age_category_id);
                $competition_category = ArcheryMasterCompetitionCategory::find($cd->competition_category_id);
                $distance = ArcheryMasterDistanceCategory::find($cd->distance_id);
                $group_category_name = $competition_category->label . " - " . $age_category->label . " - " . $distance->label;

                $response_category["competition_category_id"] = $competition_category->id;
                $response_category["age_category_id"] = $age_category->id;
                $response_category["distance_id"] = $distance->id;
                $response_category["label"] = $group_category_name;
                $list_categories[] = $response_category;
            }
        }
        $response["list_category"] = $list_categories;

        return $response;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
        ];
    }

    private function setNormalRules($event_id)
    {
        $categories = ArcheryEventCategoryDetail::where("event_id", $event_id)->get();
        foreach ($categories as $c) {
            $c->rating_flag = 1;
            $c->rules_rating_club = 1;
            $c->group_category_id = 0;
            $c->save();
        }
    }
}
