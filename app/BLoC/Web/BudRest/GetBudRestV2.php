<?php

namespace App\BLoC\Web\BudRest;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventQualificationTime;
use App\Models\BudRest;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetBudRestV2 extends Transactional
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
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $list_schedule = ArcheryEventQualificationTime::select("archery_event_qualification_time.*")
            ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_qualification_time.category_detail_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->where("archery_master_team_categories.type", "Individual")
            ->get();

        $output = [];
        $response = [];

        if ($list_schedule->count() > 0) {
            foreach ($list_schedule as $schedule) {
                $bud_rest = BudRest::where("archery_event_category_id", $schedule->category_detail_id)->first();
                $category = ArcheryEventCategoryDetail::find($schedule->category_detail_id);

                $detail_category = null;

                $detail_budrest = null;
                if ($bud_rest) {
                    $detail_budrest = [
                        "id" => $bud_rest->id,
                        "bud_rest_start" => $bud_rest->bud_rest_start,
                        "bud_rest_end" => $bud_rest->bud_rest_end,
                        "target_face" => $bud_rest->target_face,
                        "type" => $bud_rest->type,
                    ];
                }

                if ($category) {
                    $detail_category = [
                        "id" => $category->id,
                        "label" => $category->label_category,
                        "event_start" => $schedule->event_start_datetime,
                        "total_participant" => ArcheryEventParticipant::getTotalPartisipantByEventByCategory($category->id)
                    ];
                }

                $response["bud_rest"] = $detail_budrest;
                $response["detail_category"] = $detail_category;

                array_push($output, $response);
            }
        }

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required"
        ];
    }
}
