<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;


use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventMasterAgeCategory;
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

        $age_categories = ArcheryMasterAgeCategory::where("eo_id", 0)
            ->orWhere("eo_id", $admin->id)
            ->orderBy("eo_id")
            ->orderBy("id")
            ->get();

        return $age_categories;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
