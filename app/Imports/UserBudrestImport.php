<?php

namespace App\Imports;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserBudrestImport implements ToCollection, WithHeadingRow
{
    private $fail_import = [];
    public function __construct(int $event_id)
    {
        $this->event_id = $event_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            $name = $row["name"];
            $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_event_qualification_time.id as qualification_time_id")
                ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                ->join("archery_master_competition_categories", "archery_master_competition_categories.id", "=", "archery_event_category_details.competition_category_id")
                ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
                ->join("archery_master_distances", "archery_master_distances.id", "=", "archery_event_category_details.distance_id")
                ->join("archery_event_qualification_time", "archery_event_qualification_time.category_detail_id", "=", "archery_event_category_details.id")
                ->where("event_id", $this->event_id)
                ->where("archery_master_team_categories.id", $row["team_category"])
                ->where("archery_master_competition_categories.id", $row["competition_category"])
                ->where("archery_master_age_categories.id", $row["age_category"])
                ->where("archery_master_distances.id", $row["distance_category"])
                ->first();

            if (!$category) {
                throw new BLoCException("category not found " . $key);
            }


            $member = ArcheryEventParticipantMember::select("archery_event_participant_members.*")
                ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
                ->join("users", "users.id", "=", "archery_event_participants.user_id")
                ->whereRaw("users.name like ?", ["%" . $name . "%"])
                ->where("archery_event_participants.event_category_id", $category->id)
                ->where("archery_event_participants.status", 1)
                ->first();

            if (!$member) {
                $this->fail_import[$key]["name"] = $name;
                $this->fail_import[$key]["index"] = $key;
                continue;
                // throw new BLoCException("member not found for name " . $name . " on index " . $key);
            }


            $schedule_full_day = ArcheryEventQualificationScheduleFullDay::where("qalification_time_id", $category->qualification_time_id)
                ->where("participant_member_id", $member->id)
                ->first();

            $bud_rest = 0;
            $target_face = "";

            // split budrest number dan target face
            $brn = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $row["bud_rest_number"]);
            if (count($brn) == 1) {
                if (ctype_alpha($brn[0])) {
                    throw new BLoCException("bantalan harus mengandung angka");
                }
                $bud_rest = $brn[0];
            } elseif (count($brn) == 2) {
                $bud_rest = $brn[0];
                $target_face = $brn[1];
            } else {
                throw new BLoCException("input invalid");
            }

            if (!$schedule_full_day) {
                $schedule_full_day = new ArcheryEventQualificationScheduleFullDay();
            }

            $schedule_full_day->qalification_time_id = $category->qualification_time_id;
            $schedule_full_day->participant_member_id = $member->id;
            $schedule_full_day->bud_rest_number = $bud_rest;
            $schedule_full_day->target_face = $target_face;
            $schedule_full_day->save();
        }
    }

    public function getFailImport()
    {
        return $this->fail_import;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            "0" => "required|string",
            '1' => "required|string",
            "2" => "required|exists:archery_master_competition_categories,id",
            "3" => "required|exists:archery_master_age_categories,id",
            "4" => "required|exists:archery_master_distances,id",
            "5" => "required|exists:archery_master_team_categories,id",
        ];
    }

    public function customValidationAttributes()
    {
        return [
            "0" => "bud_rest_number",
            '1' => 'name',
            "2" => "competition_category",
            "3" => "age_category",
            "4" => "distance_category",
            "5" => "team_category"
        ];
    }
}
