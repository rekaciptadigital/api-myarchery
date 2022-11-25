<?php

namespace App\Imports;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryScoring;
use App\Models\ConfigTargetFace;
use App\Models\ConfigTargetFacePerCategory;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MemberScoringImport implements ToCollection, WithHeadingRow
{
    private $fail_import = [];

    public function __construct(int $category_id)
    {
        $this->category_id = $category_id;
    }

    public function collection(Collection $rows)
    {
        $category_id = $this->category_id;
        $category = ArcheryEventCategoryDetail::find($category_id);
        foreach ($rows as $key => $row) {
            $name = $row["name"];

            $member = ArcheryEventParticipantMember::select("archery_event_participant_members.*")
                ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
                ->join("users", "users.id", "=", "archery_event_participants.user_id")
                ->whereRaw("users.name like ?", ["%" . $name . "%"])
                ->where("archery_event_participants.event_category_id", $category_id)
                ->where("archery_event_participants.status", 1)
                ->first();

            if (!$member) {
                $this->fail_import[$key]["name"] = $name;
                continue;
            }

            // get config rambahan arrow ==========================================================
            $score_x_value = 10;
            $config_target_face = ConfigTargetFace::where("event_id", $category->event_id)
                ->first();
            if ($config_target_face) {
                $score_x_value = $config_target_face->score_x;
                if ($config_target_face->implement_all == 0) {
                    $config_target_face_per_category = ConfigTargetFacePerCategory::where("config_id", $config_target_face)->get();
                    foreach ($config_target_face_per_category as $ctfpc) {
                        if (
                            $category->distance_id == $ctfpc->distance_id
                            && $category->competition_category_id == $ctfpc->competition_category_id
                            && $category->age_category_id == $ctfpc->age_category_id
                        ) {
                            $score_x_value = $ctfpc->score_x;
                        }
                    }
                }
            }


            // cek schedule =================================================================================
            $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $member->id)
                ->first();
            if (!$schedule) {
                throw new BLoCException("jadwal belum di set");
            }
            // ===================================================================

            // susun param ======================================
            $rambahan_1_session_1 = [];
            $rambahan_2_session_1 = [];
            $rambahan_3_session_1 = [];
            $rambahan_4_session_1 = [];
            $rambahan_5_session_1 = [];
            $rambahan_6_session_1 = [];
            $rambahan_1_session_2 = [];
            $rambahan_2_session_2 = [];
            $rambahan_3_session_2 = [];
            $rambahan_4_session_2 = [];
            $rambahan_5_session_2 = [];
            $rambahan_6_session_2 = [];
            for ($s = 1; $s <= 72; $s++) {
                if ($s >= 1 && $s <= 6) {
                    $rambahan_1_session_1[] = $row[$s];
                }

                if ($s >= 7 && $s <= 12) {
                    $rambahan_2_session_1[] = $row[$s];
                }

                if ($s >= 13 && $s <= 18) {
                    $rambahan_3_session_1[] = $row[$s];
                }

                if ($s >= 19 && $s <= 24) {
                    $rambahan_4_session_1[] = $row[$s];
                }

                if ($s >= 25 && $s <= 30) {
                    $rambahan_5_session_1[] = $row[$s];
                }

                if ($s >= 31 && $s <= 36) {
                    $rambahan_6_session_1[] = $row[$s];
                }

                if ($s >= 37 && $s <= 42) {
                    $rambahan_1_session_2[] = $row[$s];
                }

                if ($s >= 43 && $s <= 48) {
                    $rambahan_2_session_2[] = $row[$s];
                }

                if ($s >= 49 && $s <= 54) {
                    $rambahan_3_session_2[] = $row[$s];
                }

                if ($s >= 55 && $s <= 60) {
                    $rambahan_4_session_2[] = $row[$s];
                }

                if ($s >= 61 && $s <= 66) {
                    $rambahan_5_session_2[] = $row[$s];
                }

                if ($s >= 67 && $s <= 72) {
                    $rambahan_6_session_2[] = $row[$s];
                }
            }
            
            // ====================================================================

            // insert session 1 =========================================================================
            $shot_scores_session_1 = [
                "1" => $rambahan_1_session_1,
                "2" => $rambahan_2_session_1,
                "3" => $rambahan_3_session_1,
                "4" => $rambahan_4_session_1,
                "5" => $rambahan_5_session_1,
                "6" => $rambahan_6_session_1,
            ];

            $get_score_session_1 = ArcheryScoring::where("scoring_session", 1)
                ->where("participant_member_id", $member->id)
                ->where('type', 1)
                ->first();

                
            $score_session_1 = ArcheryScoring::makeScoring($shot_scores_session_1, $score_x_value);
            if ($get_score_session_1) {
                $scoring_session_1 = ArcheryScoring::find($get_score_session_1->id);
            } else {
                $scoring_session_1 = new ArcheryScoring;
            }

            $scoring_session_1->participant_member_id = $member->id;
            $scoring_session_1->total = $score_session_1->total;
            $scoring_session_1->total_tmp = $score_session_1->total_tmp_string;
            $scoring_session_1->scoring_session = 1;
            $scoring_session_1->type = 1;
            $scoring_session_1->item_value = "archery_event_qualification_schedule_full_day";
            $scoring_session_1->item_id = $schedule->id;
            $scoring_session_1->scoring_detail = \json_encode($score_session_1->scors);
            $scoring_session_1->save();
            // ===============================================================================



            // insert session2 =============================================================================
            $shot_scores_session_2 = [
                "1" => $rambahan_1_session_2,
                "2" => $rambahan_2_session_2,
                "3" => $rambahan_3_session_2,
                "4" => $rambahan_4_session_2,
                "5" => $rambahan_5_session_2,
                "6" => $rambahan_6_session_2,
            ];


            $get_score_session_2 = ArcheryScoring::where("scoring_session", 2)->where("participant_member_id", $member->id)->where('type', 1)->first();

            $score_session_2 = ArcheryScoring::makeScoring($shot_scores_session_2, $score_x_value);


            if ($get_score_session_2) {
                $scoring_session_2 = ArcheryScoring::find($get_score_session_2->id);
            } else {
                $scoring_session_2 = new ArcheryScoring;
            }

            $scoring_session_2->participant_member_id = $member->id;
            $scoring_session_2->total = $score_session_1->total;
            $scoring_session_2->total_tmp = $score_session_1->total_tmp_string;
            $scoring_session_2->scoring_session = 2;
            $scoring_session_2->type = 1;
            $scoring_session_2->item_value = "archery_event_qualification_schedule_full_day";
            $scoring_session_2->item_id = $schedule->id;
            $scoring_session_2->scoring_detail = \json_encode($score_session_2->scors);
            $scoring_session_2->save();
            // =====================================================================================
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
}
