<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;


use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryMasterAgeCategory;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetDetailMasterAgeCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $age_category_id = $parameters->get("age_category_id");

        $age_category = ArcheryMasterAgeCategory::find($age_category_id);
        if (!$age_category) {
            throw new BLoCException("age category not found");
        }


        if ($age_category->eo_id != 0 && $age_category->eo_id != $admin->id) {
            throw new BLoCException("forbiden");
        }


        return $age_category;
    }

    protected function validation($parameters)
    {
        return [
            "age_category_id" => "required"
        ];
    }
}
