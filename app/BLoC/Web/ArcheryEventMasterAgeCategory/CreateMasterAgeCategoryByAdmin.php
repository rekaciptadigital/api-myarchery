<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;


use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryMasterAgeCategory;
use DAI\Utils\Exceptions\BLoCException;
use DateTime;
use Illuminate\Support\Facades\Auth;

class CreateMasterAgeCategoryByAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $type = $parameters->get("type");
        $label = trim($parameters->get("label"));
        $is_age = $parameters->get("is_age");
        $min = $parameters->get("min");
        $max = $parameters->get("max");
        $eo_id = $admin->id;

        $is_exist = ArcheryMasterAgeCategory::where("label", $label)->where("eo_id", $eo_id)
            ->where("is_hide", 0)
            ->first();
        if ($is_exist) {
            throw new BLoCException("category " . $label . " sudah dibuat sebelumnya");
        }
        $digits = 4;
        $id = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        $category = new ArcheryMasterAgeCategory;
        if ($type == "usia") {
            if ($is_age == 1) {
                if (($min > 0 && $max > 0) && $min > $max) {
                    throw new BLoCException("min harus lebih kecil dari max");
                }
                $category->min_age = $min;
                $category->max_age = $max;
            } else {
                $datetime_min = DateTime::createFromFormat("Y-m-d H:i:s", $min);
                $datetime_max = DateTime::createFromFormat("Y-m-d H:i:s", $max);
                if ($datetime_min && $datetime_max) {
                    if ($datetime_min > $datetime_max) {
                        throw new BLoCException("date min must be lower than date max");
                    }
                } elseif ($datetime_min && $max != 0) {
                    throw new BLoCException("invalid 1");
                } elseif ($datetime_max && $min != 0) {
                    throw new BLoCException("invalid 2");
                } elseif (!$datetime_min && !$datetime_max) {
                    throw new BLoCException("invalid 3");
                }

                $category->min_date_of_birth = $min == 0 ? null : $min;
                $category->max_date_of_birth = $max == 0 ? null : $max;
                $category->is_age = $is_age;
            }
        }
        $category->id = $id;
        $category->label = $label;
        $category->eo_id = $eo_id;
        $category->save();





        return $category;
    }

    protected function validation($parameters)
    {
        $rules = [
            "label" => "required",
            "type" => "required|in:umum,usia",
        ];
        if (!is_null($parameters->get("type")) && $parameters->get("type") != "umum") {
            $rules["is_age"] = "required|in:1,0";
            if ($parameters->get("is_age") == 1) {
                $rules["min"] = "required|numeric";
                $rules["max"] = "required|numeric";
            } else {
                $rules["min"] = "required";
                $rules["max"] = "required";
            }
        }

        return $rules;
    }
}
