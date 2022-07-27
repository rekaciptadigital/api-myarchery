<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventMasterAgeCategory;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterAgeCategory;
use DAI\Utils\Exceptions\BLoCException;
use DateTime;
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
        $type = $parameters->get("type");
        $label = trim($parameters->get("label"));
        $is_age = $parameters->get("is_age");
        $min = $parameters->get("min");
        $max = $parameters->get("max");

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        $age_category = ArcheryMasterAgeCategory::find($id);
        if (!$age_category) {
            throw new BLoCException("age category not found");
        }

        if ($age_category->eo_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $category_details = ArcheryEventCategoryDetail::select("archery_event_category_details.*")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
            ->where("archery_event_category_details.event_id", "!=", $event_id)
            ->where("age_category_id", $age_category->id)
            ->get();

        if ($category_details->count() > 0) {
            throw new BLoCException("tidak dapat melakukan update karena telah ada event yang menggunakan kategori umur ini");
        }

        $time_now = time();
        $participant_register = ArcheryEventParticipant::select("archery_event_participants.*")->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where("event_id", $event_id)
            ->where("age_category_id", $age_category->id)
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_participants.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("archery_event_participants.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            })->get();

        if ($participant_register->count() > 0) {
            throw new BLoCException("tidak dapat diubah karena telah ada yang mendaftar di kategori umur tersebut");
        }

        $is_exist = ArcheryMasterAgeCategory::where("label", $label)->where("eo_id", $admin->id)->where("id", "!=", $age_category->id)->first();
        if ($is_exist) {
            throw new BLoCException("category " . $label . " sudah dibuat sebelumnya");
        }

        if ($type == "usia") {
            if ($is_age == 1) {
                $age_category->min_date_of_birth = null;
                $age_category->max_date_of_birth = null;
                $age_category->min_age = $min;
                $age_category->max_age = $max;
            } else {
                $age_category->min_age = 0;
                $age_category->max_age = 0;
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

                $age_category->min_date_of_birth = $min == 0 ? null : $min;
                $age_category->max_date_of_birth = $max != 0 ? $max : null;
            }
        } else {
            $age_category->min_date_of_birth = null;
            $age_category->max_date_of_birth = null;
            $age_category->min_age = 0;
            $age_category->max_age = 0;
        }
        $age_category->is_age = $is_age;
        $age_category->label = $label;
        $age_category->save();

        return $age_category;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required"
        ];
    }
}
