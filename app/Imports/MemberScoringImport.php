<?php

namespace App\Imports;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MemberScoringImport implements ToCollection, WithHeadingRow
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
            }
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
        ];
    }

    public function customValidationAttributes()
    {
        return [
            "0" => "name",
        ];
    }
}
