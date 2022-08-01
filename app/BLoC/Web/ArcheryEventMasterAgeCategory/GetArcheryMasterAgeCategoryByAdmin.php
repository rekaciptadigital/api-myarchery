<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;


use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryMasterAgeCategory;
use Illuminate\Support\Facades\Auth;

class GetArcheryMasterAgeCategoryByAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $show_all = $parameters->get("show_all");

        $age_categories = ArcheryMasterAgeCategory::where(function ($q) use ($admin) {
            $q->where("eo_id", 0)
                ->orWhere("eo_id", $admin->id);
        })->where(function ($query) use ($show_all) {
            if (!isset($show_all) || !$show_all == 1) {
                $query->where("is_hide", 0);
            }
        })->orderBy("eo_id")
            ->orderBy("id")
            ->get();

        return $age_categories;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
