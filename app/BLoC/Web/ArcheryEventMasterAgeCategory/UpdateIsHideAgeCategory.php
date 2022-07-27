<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;


use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryMasterAgeCategory;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateIsHideAgeCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $age_category_id = $parameters->get("age_category_id");
        $is_hide = $parameters->get("is_hide");

        $age_category = ArcheryMasterAgeCategory::where("id", $age_category_id)->where("eo_id", $admin->id)->first();
        if (!$age_category) {
            throw new BLoCException("age category not found");
        }

        $age_category->is_hide = $is_hide;
        $age_category->save();

        return $age_category;
    }

    protected function validation($parameters)
    {
        return [
            "age_category_id" => "required",
            "is_hide" => "required|in:1,0"
        ];
    }
}
