<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventMasterCompetitionCategory;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class CreateOrUpdateArcheryCategoryDetailV2 extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event = ArcheryEvent::find($parameters->get("event_id"));

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("Forbiden");
        }

        $event->include_payment_gateway_fee_to_user = empty($parameters->get("include_payment_gateway_fee_to_user")) ? 0 : $parameters->get("include_payment_gateway_fee_to_user");
        $event->include_my_archery_fee_to_user = empty($parameters->get("include_my_archery_fee_to_user")) ? 0 : $parameters->get("include_my_archery_fee_to_user");
        $event->save();

        $list_category = $parameters->get("categories", []);
        if (count($list_category) == 0) {
            throw new BLoCException("harap inputkan minimal 1 kategory lomba");
        }

        foreach ($list_category as $key => $category) {
            $competitio_category = ArcheryEventMasterCompetitionCategory::find($category['competition_category_id']);
            $age_category = ArcheryMasterAgeCategory::find($category['age_category_id']);

            if ($age_category->is_hide == 1) {
                throw new BLoCException("kategori umur ini tidak dapat digunakan");
            }

            $distance_category = ArcheryMasterDistanceCategory::find($category['distance_category_id']);
            $team_category = ArcheryMasterTeamCategory::find($category['team_category_id']);

            $end_early_bird = $category["end_date_early_bird"];
            $early_bird = $category["early_bird"];

            // pengecekan apakah tanggal dan harga earlybird ditentukan oleh admin
            if ($end_early_bird != null) {
                if ($early_bird == 0) {
                    throw new BLoCException("harga early bird harus lebih besar dari 0");
                }
            } elseif ($early_bird > 0) {
                if ($end_early_bird == null) {
                    throw new BLoCException("harap inputkan tanggal early bird");
                }
            }

            $archery_category_detail = ArcheryEventCategoryDetail::where("age_category_id", $age_category->id)
                ->where("competition_category_id", $competitio_category->id)
                ->where("distance_id", $distance_category->id)
                ->where("team_category_id", $team_category->id)
                ->where("event_id", $event->id)
                ->first();

            if (!$archery_category_detail) {
                $archery_category_detail = new ArcheryEventCategoryDetail();
            } else {
                $count_user_join_or_order_category = ArcheryEventParticipant::select("archery_event_participants.*")
                    ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where("archery_event_participants.event_category_id ", $archery_category_detail->id)
                    ->where(function ($query) {
                        $query->where("archery_event_participants.status", 1)
                            ->orWhere(function ($q) {
                                $q->where("archery_event_participants.status", 4)
                                    ->where("transaction_logs.status", 4)
                                    ->where("transaction_logs.expired_time", ">", time());
                            });
                    })->get()
                    ->count();

                if ($count_user_join_or_order_category > 0) { // cek apakah telah ada user yang daftar di category tsb
                    // cek apakah terjadi perubahan harga
                    if (
                        $archery_category_detail->fee != $category['fee']
                        || $archery_category_detail->early_bird != $category['early_bird']
                        || $archery_category_detail->end_date_early_bird != $category['end_date_early_bird']
                    ) {
                        throw new BLoCException("tidak dapat ubah harga karena telah ada peserta yang mendaftar");
                    }
                }
            }

            $archery_category_detail->event_id = $event->id;
            $archery_category_detail->age_category_id = $age_category->id;
            $archery_category_detail->min_age = $age_category->min_age;
            $archery_category_detail->max_age = $age_category->max_age;
            $archery_category_detail->min_date_of_birth = $age_category->min_date_of_birth;
            $archery_category_detail->max_date_of_birth = $age_category->max_date_of_birth;
            $archery_category_detail->competition_category_id = $competitio_category->id;
            $archery_category_detail->distance_id  = $distance_category->id;
            $archery_category_detail->team_category_id  = $team_category->id;
            if ($team_category->type == "Team") {
                $archery_category_detail->qualification_mode = "best_of_three";
            }
            $archery_category_detail->quota = $category['quota'];
            $archery_category_detail->fee = $category['fee'];
            $archery_category_detail->is_show = $category["is_show"];
            $archery_category_detail->early_bird = $category["early_bird"];
            $archery_category_detail->end_date_early_bird = $end_early_bird;
            $archery_category_detail->save();
        }



        return ArcheryEvent::detailEventById($event->id);
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer|exists:archery_events,id',
            "categories" => "required|array|min:1",
            'categories.*.age_category_id' => 'required|exists:archery_master_age_categories,id',
            'categories.*.competition_category_id' => 'required|exists:archery_master_competition_categories,id',
            'categories.*.distance_category_id' => 'required|exists:archery_master_distances,id',
            'categories.*.team_category_id' => 'required|exists:archery_master_team_categories,id',
            'categories.*.quota' => 'required|min:0',
            'categories.*.fee' => 'required|min:0',
            'categories.*.early_bird' => "required|min:0",
        ];
    }
}
