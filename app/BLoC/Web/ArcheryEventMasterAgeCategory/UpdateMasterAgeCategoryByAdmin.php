<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventMasterAgeCategory;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterAgeCategory;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateMasterAgeCategoryByAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $id = $parameters->get("id");
        $event_id = $parameters->get("event_id");

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        $age_category = ArcheryMasterAgeCategory::find($id);
        if (!$age_category) {
            throw new BLoCException("age category not found");
        }

        $category_details = ArcheryEventCategoryDetail::select("archery_event_category_details.*")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->where("age_category_id", $age_category->id)
            ->get();

        foreach ($category_details as $cd) {
            // ArcheryEventParticipant::where("event_id", $event_id)->where()
        }
    }

    protected function validation($parameters)
    {
        return [];
    }
}
