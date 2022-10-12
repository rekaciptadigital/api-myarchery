<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Exceptions\BLoCException;

class ArcheryScoring extends Model
{

    protected $guarded = ["id"];
    protected $score_value = [
        "" => 0,
        "1" => 1,
        "2" => 2,
        "3" => 3,
        "4" => 4,
        "5" => 5,
        "6" => 6,
        "7" => 7,
        "8" => 8,
        "9" => 9,
        "10" => 10,
        "x" => 10,
        "m" => 0,
    ];

    protected $elimination_scores_format_by_type = [
        "1" => [
            "shot" => [
                ["score" => ["", "", ""], "total" => 0, "status" => "empty", "point" => 0], // status = ["empty","win","draw","lose"]
                ["score" => ["", "", ""], "total" => 0, "status" => "empty", "point" => 0],
                ["score" => ["", "", ""], "total" => 0, "status" => "empty", "point" => 0],
                ["score" => ["", "", ""], "total" => 0, "status" => "empty", "point" => 0],
                ["score" => ["", "", ""], "total" => 0, "status" => "empty", "point" => 0]
            ],
            "extra_shot" => [
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"]
            ],
            "win" => 0,
            "total" => 0,
            "result" => 0,
            "eliminationt_score_type" => 1
        ],

        "2" => [
            "shot" => [
                ["score" => ["", "", "",], "total" => 0],
                ["score" => ["", "", "",], "total" => 0],
                ["score" => ["", "", "",], "total" => 0],
                ["score" => ["", "", "",], "total" => 0],
                ["score" => ["", "", "",], "total" => 0]
            ],
            "extra_shot" => [
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"]
            ],
            "win" => 0,
            "total" => 0,
            "result" => 0,
            "eliminationt_score_type" => 2
        ]
    ];

    protected $score_type = array(
        array("id" => 1, "value" => "qualification"),
        array("id" => 2, "value" => "elimination")
    );

    protected function ArcheryScoringDetailPoint()
    {
        return [
            "" => 0,
            "1" => 0,
            "2" => 0,
            "3" => 0,
            "4" => 0,
            "5" => 0,
            "6" => 0,
            "7" => 0,
            "8" => 0,
            "9" => 0,
            "10" => 0,
            "x" => 0,
            "m" => 0,
        ];
    }
    protected function makeScoringFormat(object $scoring, $session = null, $count_stage = 6, $count_shot_in_stage = 6)
    {
        $scores = [];
        // print_r(json_encode($scoring));
        if (empty((array)$scoring)) {
            if ($session !== null && $session == 11) {
                $scores = [];
                for ($i = 0; $i < 5; $i++) {
                    $res = [
                        "score" => "",
                        "distance_from_x" => ""
                    ];
                    array_push($scores, $res);
                }
                return $scores;
            }
            for ($i = 0; $i < $count_stage; $i++) {
                $score_space = [];
                for ($x = 0; $x < $count_shot_in_stage; $x++) {
                    $score_space[] = "";
                }
                $scores[$i + 1] = $score_space;
            }

            return $scores;
        }
        // throw new BLoCException($session);
        if ($session == 11) {
            foreach ($scoring as $key => $value) {
                $scores[$key] = $value;
            }
            return $scores;
        }

        foreach ($scoring as $key => $value) {
            // throw new BLoCException("ok");
            $score = [];
            foreach ($value as $k => $v) {
                $score[] = (string)$v->id;
            }
            $scores[$key] = $score;
        }
        return $scores;
    }

    protected function makeEliminationScoringTypePointFormat()
    {
        $scores = $this->elimination_scores_format_by_type[1];
        return $scores;
    }

    protected function calculateEliminationScoringTypePointFormat(array $scoring_1, array $scoring_2, $save_permanent)
    {
        $scores = $this->elimination_scores_format_by_type[1];
        $total_point_1 = 0;
        $total_point_2 = 0;
        $total_score_1 = 0;
        $total_score_2 = 0;
        $win_1 = 0;
        $win_2 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;
            $scoring_2_total_score_per_rambahan = 0;
            $point_1_per_rambahan = 0;
            $point_2_per_rambahan = 0;
            foreach ($shot["score"] as $i => $s) {
                $s1 = $this->score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
                $s2 = $this->score_value[$scoring_2["scores"]["shot"][$k]["score"][$i]];
                $scoring_2_total_score_per_rambahan = $scoring_2_total_score_per_rambahan + $s2;
            }

            $status_1 = "empty";
            $status_2 = "empty";

            $total_score_1 = $total_score_1 + $scoring_1_total_score_per_rambahan;
            $total_score_2 = $total_score_2 + $scoring_2_total_score_per_rambahan;
            $scoring_1["scores"]["shot"][$k]["total"] = $scoring_1_total_score_per_rambahan;
            $scoring_2["scores"]["shot"][$k]["total"] = $scoring_2_total_score_per_rambahan;

            if ($scoring_1_total_score_per_rambahan != 0 || $scoring_2_total_score_per_rambahan != 0) {
                if ($scoring_1_total_score_per_rambahan > $scoring_2_total_score_per_rambahan) {
                    $status_1 = "win";
                    $status_2 = "lose";
                    $point_1_per_rambahan = $point_1_per_rambahan + 2;
                    $point_2_per_rambahan = $point_2_per_rambahan + 0;
                }
                if ($scoring_2_total_score_per_rambahan > $scoring_1_total_score_per_rambahan) {
                    $status_1 = "lose";
                    $status_2 = "win";
                    $point_1_per_rambahan = $point_1_per_rambahan + 0;
                    $point_2_per_rambahan = $point_2_per_rambahan + 2;
                }
                if ($scoring_1_total_score_per_rambahan == $scoring_2_total_score_per_rambahan) {
                    $status_1 = "draw";
                    $status_2 = "draw";
                    $point_1_per_rambahan = $point_1_per_rambahan + 1;
                    $point_2_per_rambahan = $point_2_per_rambahan + 1;
                }
            }

            $scoring_1["scores"]["shot"][$k]["status"] = $status_1;
            $scoring_2["scores"]["shot"][$k]["status"] = $status_2;
            $total_point_1 = $total_point_1 + $point_1_per_rambahan;
            $total_point_2 = $total_point_2 + $point_2_per_rambahan;
            $scoring_1["scores"]["shot"][$k]["point"] = $point_1_per_rambahan;
            $scoring_2["scores"]["shot"][$k]["point"] = $point_2_per_rambahan;
        }

        if (($total_point_1 != 0 || $total_point_2 != 0) && $total_point_1 < $total_point_2) {
            $win_2 = 1;
        }
        if (($total_point_1 != 0 || $total_point_2 != 0) && $total_point_1 > $total_point_2) {
            $win_1 = 1;
        }

        if ($total_point_1 != 0 && $total_point_2 != 0 && $total_point_1 == $total_point_2) {
            foreach ($scores["extra_shot"] as $es => $extra_shot) {
                $es_score_1 = $scoring_1["scores"]["extra_shot"][$es]["score"] != "x" ? $this->score_value[$scoring_1["scores"]["extra_shot"][$es]["score"]] : 11;
                $es_score_2 = $scoring_2["scores"]["extra_shot"][$es]["score"] != "x" ? $this->score_value[$scoring_2["scores"]["extra_shot"][$es]["score"]] : 11;
                $es_distance_1 = $scoring_1["scores"]["extra_shot"][$es]["distance_from_x"];
                $es_distance_2 = $scoring_2["scores"]["extra_shot"][$es]["distance_from_x"];
                $es_status_2 = "draw";
                $es_status_1 = "draw";
                if ($es_score_1 == 0 && $es_score_2 == 0)
                    break;

                // $total_score_1 = $total_score_1 + ($es_score_1 == 11 ? 10 : $es_score_1);
                // $total_score_2 = $total_score_2 + ($es_score_2 == 11 ? 10 : $es_score_2);
                if ($es_score_2 == $es_score_1) {
                    if ($es_distance_1 < $es_distance_2) {
                        $es_status_2 = "lose";
                        $es_status_1 = "win";
                        $win_1 = 1;
                        $win_2 = 0;
                        break;
                    }
                    if ($es_distance_2 < $es_distance_1) {
                        $es_status_2 = "win";
                        $es_status_1 = "lose";
                        $win_1 = 0;
                        $win_2 = 1;
                        break;
                    }
                }
                if ($es_score_2 > $es_score_1) {
                    $es_status_2 = "win";
                    $es_status_1 = "lose";
                    $win_1 = 0;
                    $win_2 = 1;
                    break;
                }
                if ($es_score_1 > $es_score_2) {
                    $es_status_2 = "lose";
                    $es_status_1 = "win";
                    $win_1 = 1;
                    $win_2 = 0;
                    break;
                }
                $scoring_1["scores"]["extra_shot"][$es]["status"] = $es_status_1;
                $scoring_2["scores"]["extra_shot"][$es]["status"] = $es_status_2;
            }
        }

        if ($save_permanent == 1) {
            $scoring_1["scores"]["win"] = $win_1;
            $scoring_2["scores"]["win"] = $win_2;
        }

        $scoring_1["scores"]["total"] = $total_score_1;
        $scoring_2["scores"]["total"] = $total_score_2;

        $scoring_1["scores"]["result"] = $total_point_1;
        $scoring_2["scores"]["result"] = $total_point_2;

        $scoring_1["scores"]["eliminationt_score_type"] = 1;
        $scoring_2["scores"]["eliminationt_score_type"] = 1;

        return [
            $scoring_1["member_id"] => $scoring_1,
            $scoring_2["member_id"] => $scoring_2,
        ];
    }

    protected function calculateEliminationScoringTypePointFormatBye(array $scoring_1)
    {
        $scores = $this->elimination_scores_format_by_type[1];
        $total_point_1 = 0;
        $total_score_1 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;

            $point_1_per_rambahan = 0;

            foreach ($shot["score"] as $i => $s) {
                $s1 = $this->score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
            }
            $total_score_1 = $total_score_1 + $scoring_1_total_score_per_rambahan;
            $scoring_1["scores"]["shot"][$k]["total"] = $scoring_1_total_score_per_rambahan;

            $total_point_1 = $total_point_1 + $point_1_per_rambahan;
            $scoring_1["scores"]["shot"][$k]["point"] = $point_1_per_rambahan;
        }

        $scoring_1["scores"]["total"] = $total_score_1;


        $scoring_1["scores"]["result"] = $total_point_1;


        $scoring_1["scores"]["eliminationt_score_type"] = 1;


        return [
            $scoring_1["member_id"] => $scoring_1,

        ];
    }

    protected function calculateEliminationScoringTypeTotalFormat(array $scoring_1, array $scoring_2, $save_permanent)
    {
        $scores = $this->elimination_scores_format_by_type[2];
        $total_score_1 = 0;
        $total_score_2 = 0;
        $win_1 = 0;
        $win_2 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;
            $scoring_2_total_score_per_rambahan = 0;
            foreach ($shot["score"] as $i => $s) {
                $s1 = $this->score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
                $s2 = $this->score_value[$scoring_2["scores"]["shot"][$k]["score"][$i]];
                $scoring_2_total_score_per_rambahan = $scoring_2_total_score_per_rambahan + $s2;
            }

            $status_1 = "empty";
            $status_2 = "empty";

            $total_score_1 = $total_score_1 + $scoring_1_total_score_per_rambahan;
            $total_score_2 = $total_score_2 + $scoring_2_total_score_per_rambahan;
            $scoring_1["scores"]["shot"][$k]["total"] = $scoring_1_total_score_per_rambahan;
            $scoring_2["scores"]["shot"][$k]["total"] = $scoring_2_total_score_per_rambahan;
        }

        if (($total_score_1 != 0 || $total_score_2 != 0) && $total_score_1 < $total_score_2) {
            $win_2 = 1;
        }
        if (($total_score_1 != 0 || $total_score_2 != 0) && $total_score_1 > $total_score_2) {
            $win_1 = 1;
        }

        if ($total_score_1 != 0 && $total_score_2 != 0 && $total_score_1 == $total_score_2) {
            foreach ($scores["extra_shot"] as $es => $extra_shot) {
                $es_score_1 = $scoring_1["scores"]["extra_shot"][$es]["score"] != "x" ? $this->score_value[$scoring_1["scores"]["extra_shot"][$es]["score"]] : 11;
                $es_score_2 = $scoring_2["scores"]["extra_shot"][$es]["score"] != "x" ? $this->score_value[$scoring_2["scores"]["extra_shot"][$es]["score"]] : 11;
                $es_distance_1 = $scoring_1["scores"]["extra_shot"][$es]["distance_from_x"];
                $es_distance_2 = $scoring_2["scores"]["extra_shot"][$es]["distance_from_x"];
                $es_status_2 = "draw";
                $es_status_1 = "draw";
                if ($es_score_1 == 0 && $es_score_2 == 0)
                    break;

                // $total_score_1 = $total_score_1 + ($es_score_1 != 11 ? $es_score_1 : 10);
                // $total_score_2 = $total_score_2 + ($es_score_2 != 11 ? $es_score_2 : 10);
                if ($es_score_2 == $es_score_1) {
                    if ($es_distance_1 < $es_distance_2) {
                        $es_status_2 = "lose";
                        $es_status_1 = "win";
                        $win_1 = 1;
                        $win_2 = 0;
                        break;
                    }
                    if ($es_distance_2 < $es_distance_1) {
                        $es_status_2 = "win";
                        $es_status_1 = "lose";
                        $win_1 = 0;
                        $win_2 = 1;
                        break;
                    }
                }
                if ($es_score_2 > $es_score_1) {
                    $es_status_2 = "win";
                    $es_status_1 = "lose";
                    $win_1 = 0;
                    $win_2 = 1;
                    break;
                }
                if ($es_score_1 > $es_score_2) {
                    $es_status_2 = "lose";
                    $es_status_1 = "win";
                    $win_1 = 1;
                    $win_2 = 0;
                    break;
                }
                $scoring_1["scores"]["extra_shot"][$es]["status"] = $es_status_1;
                $scoring_2["scores"]["extra_shot"][$es]["status"] = $es_status_2;
            }
        }

        if ($save_permanent == 1) {
            $scoring_1["scores"]["win"] = $win_1;
            $scoring_2["scores"]["win"] = $win_2;
        }

        $scoring_1["scores"]["total"] = $total_score_1;
        $scoring_2["scores"]["total"] = $total_score_2;

        $scoring_1["scores"]["result"] = $total_score_1;
        $scoring_2["scores"]["result"] = $total_score_2;

        $scoring_1["scores"]["eliminationt_score_type"] = 2;
        $scoring_2["scores"]["eliminationt_score_type"] = 2;

        return [
            $scoring_1["member_id"] => $scoring_1,
            $scoring_2["member_id"] => $scoring_2,
        ];
    }

    protected function calculateEliminationScoringTypeTotalFormatBye(array $scoring_1)
    {
        $scores = $this->elimination_scores_format_by_type[2];
        $total_score_1 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;
            foreach ($shot["score"] as $i => $s) {
                $s1 = $this->score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
            }

            $total_score_1 = $total_score_1 + $scoring_1_total_score_per_rambahan;

            $scoring_1["scores"]["shot"][$k]["total"] = $scoring_1_total_score_per_rambahan;
        }

        $scoring_1["scores"]["total"] = $total_score_1;

        $scoring_1["scores"]["result"] = $total_score_1;

        $scoring_1["scores"]["eliminationt_score_type"] = 2;

        return [
            $scoring_1["member_id"] => $scoring_1,
        ];
    }

    protected function makeEliminationScoringTypeTotalFormat()
    {
        $scores = $this->elimination_scores_format_by_type[2];
        return $scores;
    }

    protected function makeScoring(array $scoring)
    {
        $total_per_points = [
            "" => 0,
            "1" => 0,
            "2" => 0,
            "3" => 0,
            "4" => 0,
            "5" => 0,
            "6" => 0,
            "7" => 0,
            "8" => 0,
            "9" => 0,
            "10" => 0,
            "x" => 0,
            "m" => 0,
        ];

        $scors = []; // data rambahan / keseluruhan arrow
        $total = 0;
        foreach ($scoring as $key => $value) {
            $arrows = [];
            if (!empty($value)) {
                foreach ($value as $k => $arrow) {
                    $a = isset($this->score_value[$arrow]) ? $this->score_value[$arrow] : 0;
                    $total = $total + $a;
                    $total_per_points[$arrow] = $total_per_points[$arrow] + 1;
                    $arrows[] = ["id" => $arrow, "value" => $a];
                }
                $scors[$key] = $arrows;
            }
        }

        $total_tmp = $this->getTotalTmp($total_per_points, $total);
        return (object)["total_tmp_string" => $this->getTotalTmpString($total_per_points, $total), "total_tmp" => $total_tmp, "total" => $total, "scors" => $scors];
    }

    protected function generateScoreBySession(int $participant_member_id, int $type, array $filter_session = [1, 2])
    {
        $total_per_points = [
            "" => 0,
            "1" => 0,
            "2" => 0,
            "3" => 0,
            "4" => 0,
            "5" => 0,
            "6" => 0,
            "7" => 0,
            "8" => 0,
            "9" => 0,
            "10" => 0,
            "x" => 0,
            "m" => 0,
        ];
        $member_scors = $this->where("participant_member_id", $participant_member_id)
            ->whereIn("scoring_session", $filter_session)
            ->where("type", $type)
            ->get();
        $sessions = [];
        foreach ($filter_session as $s) {
            $sessions[$s] = array(
                "score" => [],
                "total_per_point" => $total_per_points,
                "total" => 0,
                "total_tmp" => 0,
                "session" => $s,
                "total_x" => 0,
                "total_x_plus_ten" => 0
            );
        }
        $total = 0;
        $total_tmp = 0;
        $count_shot_arrows = 0;
        foreach ($member_scors as $k => $score) {
            $score_detail = json_decode($score->scoring_detail);
            $score_rambahan = [];
            $total_per_session = 0;
            foreach ($score_detail as $ks => $sd) {
                $get_score = [];
                foreach ($sd as $rambahan => $arrows) {
                    if ($arrows->id == "") {
                        continue;
                    } else {
                        $count_shot_arrows += 1;
                    }
                    $get_score[] = $arrows->id;
                    $total = $total + $arrows->value;
                    $total_per_session = $total_per_session + $arrows->value;
                    $total_per_points[$arrows->id] = $total_per_points[$arrows->id] + 1;
                    $sessions[$score->scoring_session]["total_per_point"][$arrows->id] = $sessions[$score->scoring_session]["total_per_point"][$arrows->id] + 1;
                }
                $score_rambahan[$ks] = $get_score;
            }
            $sessions[$score->scoring_session]["total_tmp"] = $this->getTotalTmp($sessions[$score->scoring_session]["total_per_point"], $total_per_session);
            $sessions[$score->scoring_session]["score"] = $score_rambahan;
            $sessions[$score->scoring_session]["total"] = $total_per_session;
            $sessions[$score->scoring_session]["scoring_id"] = $score->id;
            $sessions[$score->scoring_session]["total_x"] = $sessions[$score->scoring_session]["total_per_point"]["x"];
            $sessions[$score->scoring_session]["total_x_plus_ten"] = $sessions[$score->scoring_session]["total_per_point"]["x"] + $sessions[$score->scoring_session]["total_per_point"]["10"];
        }

        // cek apakah member tersebut melakukan shot off atau tidak
        $total_shot_off = 0;
        $total_distance_from_x = 0;
        $shot_off = ArcheryScoring::where("scoring_session", 11)->where("participant_member_id", $participant_member_id)->first();
        if ($shot_off) {
            $total_shot_off = $shot_off->total;
            $scoring_shoot_off_detail = json_decode($shot_off->scoring_detail);
            foreach ($scoring_shoot_off_detail as $key => $value) {
                $distance_from_x = $value->distance_from_x;
                if (gettype($value->distance_from_x) == "string") {
                    $distance_from_x = 0;
                }
                $total_distance_from_x = $total_distance_from_x + $distance_from_x;
            }
            $sessions["11"] = $scoring_shoot_off_detail;
        }

        $participant = ArcheryEventParticipantMember::select("archery_event_participants.*")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->where("archery_event_participant_members.id", $participant_member_id)
            ->first();

        if (!$participant) {
            throw new BLoCException("PARTICIPANT TIDAK ADA");
        }

        $total_fix = $total + $total_shot_off;

        $category_detail = ArcheryEventCategoryDetail::where('id', $participant->event_category_id)->first();
        $total_arrow = ($category_detail->count_stage * $category_detail->count_shot_in_stage) * $category_detail->session_in_qualification;
        $total_irat = $count_shot_arrows == 0 ? 0 : round(($total / $count_shot_arrows), 3);

        $output = [
            "sessions" => $sessions,
            "total_shot_off" => $participant->is_present == 1 ? $total_shot_off : 0,
            "total_distance_from_x" => $participant->is_present == 1 ? $total_distance_from_x : 0,
            "total" => $total,
            "total_x" => $total_per_points["x"],
            "total_per_points" => $total_per_points,
            "total_x_plus_ten" => $total_per_points["x"] + $total_per_points["10"],
            "total_tmp" => $participant->is_present == 1 ? $this->getTotalTmp($total_per_points, $total) : 0,
            "total_arrow" => $count_shot_arrows,
            "total_irat" => $total_irat
        ];
        return $output;
    }

    protected function makeScoringShotOffQualification($score)
    {
        $total = 0;
        $arrows = [];
        foreach ($score as $key => $value) {
            $a = isset($this->score_value[$value['score']]) ? $this->score_value[$value['score']] : 0;
            $total = $total + $a;
            $res = ["score" => $value["score"], "distance_from_x" => $value['distance_from_x']];
            array_push($arrows, $res);
        }
        return (object)["total" => $total, "scors" => $arrows];
    }

    protected function getTotalTmp(array $total_per_point, $total, $key = 0.01)
    {
        $one = $total_per_point[1];
        $two = $total_per_point[2];
        $three = $total_per_point[3];
        $four = $total_per_point[4];
        $five = $total_per_point[5];
        $six = $total_per_point[6];
        $seven = $total_per_point[7];
        $eight = $total_per_point[8];
        $nine = $total_per_point[9];
        $ten = $total_per_point[10];
        $x = $total_per_point["x"];
        $x_plus_y = $x + $ten;
        $output = $total + (
            ($x_plus_y + (
                ($x + (
                    ($ten + (
                        ($nine + (
                            ($eight + (
                                ($seven + (
                                    ($six + (
                                        ($five + (
                                            ($four + (
                                                ($three + (
                                                    ($two +
                                                        ($one * $key)
                                                    ) * $key)
                                                ) * $key)
                                            ) * $key)
                                        ) * $key)
                                    ) * $key)
                                ) * $key)
                            ) * $key)
                        ) * $key)
                    ) * $key)
                ) * $key)
            ) * $key);
        return $output;
    }

    protected function getTotalTmpString(array $total_per_point, $total)
    {
        $one = $total_per_point[1] < 10 ? "0" . $total_per_point[1] : $total_per_point[1];
        $two = $total_per_point[2] < 10 ? "0" . $total_per_point[2] : $total_per_point[2];
        $three = $total_per_point[3] < 10 ? "0" . $total_per_point[3] : $total_per_point[3];
        $four = $total_per_point[4] < 10 ? "0" . $total_per_point[4] : $total_per_point[4];
        $five = $total_per_point[5] < 10 ? "0" . $total_per_point[5] : $total_per_point[5];
        $six = $total_per_point[6] < 10 ? "0" . $total_per_point[6] : $total_per_point[6];
        $seven = $total_per_point[7] < 10 ? "0" . $total_per_point[7] : $total_per_point[7];
        $eight = $total_per_point[8] < 10 ? "0" . $total_per_point[8] : $total_per_point[8];
        $nine = $total_per_point[9] < 10 ? "0" . $total_per_point[9] : $total_per_point[9];
        $ten = $total_per_point[10] < 10 ? "0" . $total_per_point[10] : $total_per_point[10];
        $x = $total_per_point["x"] < 10 ? "0" . $total_per_point["x"] : $total_per_point["x"];
        $x_plus_y = $x + $ten;
        $x_plus_y = $x_plus_y < 10 ? "0" . $x_plus_y : $x_plus_y;
        $output = $total . " " . "" . $x_plus_y . "" . $x . "" . $ten . "" . $nine . "" . $eight . "" . $seven . "" . $six . "" . $five . "" . $four . "" . $three . "" . $two . "" . $one;
        return $output;
    }

    protected function getScoringRankByCategoryId($event_category_id, $score_type, array $sessions = [1, 2], $orderByBudrestNumber = false, $name = null, $is_present = false)
    {
        $participants_query = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.have_shoot_off",
            "users.name",
            "archery_event_participant_members.user_id",
            "users.gender",
            "archery_event_participants.id as participant_id",
            "archery_event_participants.event_id",
            "archery_event_participants.is_present",
            "archery_clubs.name as club_name",
            "archery_clubs.id as club_id",
            "archery_event_qualification_schedule_full_day.bud_rest_number",
            "archery_event_qualification_schedule_full_day.target_face"
        )
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("users", "archery_event_participant_members.user_id", "=", "users.id")
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")
            ->leftJoin("archery_event_qualification_schedule_full_day", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_category_id', $event_category_id);

        if ($name) {
            $participants_query->whereRaw("users.name LIKE ?", ["%" . $name . "%"]);
        }


        if ($orderByBudrestNumber) {
            $participants_query->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number")
                ->orderBy("archery_event_qualification_schedule_full_day.target_face");
        }

        if ($is_present) {
            $participants_query->where("archery_event_participants.is_present", 1);
        }

        $participants_collection = $participants_query->get();
        $archery_event_score = [];
        foreach ($participants_collection as $key => $value) {
            $score = $this->generateScoreBySession($value->id, $score_type, $sessions);
            $score["club_id"] = $value->club_id;
            $score["club_name"] = $value->club_name;
            $score["member"] = $value;
            $score["have_shoot_off"] = $value->have_shoot_off;
            $score["member"]["participant_number"] = ArcheryEventParticipantNumber::getNumber($value->participant_id);
            $archery_event_score[] = $score;
        }

        if (!$orderByBudrestNumber) {
            usort($archery_event_score, function ($a, $b) {
                if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                    if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                        return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                    }
                    return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
                }
                return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
            });
        }

        return $archery_event_score;
    }

    protected function getScoringRank($distance_id, $team_category_id, $competition_category_id, $age_category_id, $gender, $score_type, $event_id)
    {
        $archery_event_participant = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.name",
            "archery_event_participant_members.gender",
            "archery_event_participant_members.have_shoot_off",
            "archery_clubs.name as club_name",
            "archery_event_qualification_schedule_full_day.bud_rest_number",
            "archery_event_qualification_schedule_full_day.target_face",
            "archery_event_participants.is_present"
        )->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")
            ->leftJoin("archery_event_qualification_schedule_full_day", "archery_event_participants.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_id', $event_id);
        if (!is_null($team_category_id)) {
            $archery_event_participant->where('archery_event_participants.team_category_id', $team_category_id);
        }
        if (!is_null($distance_id)) {
            $archery_event_participant->where('archery_event_participants.distance_id', $distance_id);
        }
        if (!is_null($gender) && !empty($gender)) {
            if ($gender != "mix") {
                $archery_event_participant->where("archery_event_participant_members.gender", $gender);
            }
        }
        if (!is_null($competition_category_id)) {
            $archery_event_participant->where('archery_event_participants.competition_category_id', $competition_category_id);
        }
        if (!is_null($age_category_id)) {
            $archery_event_participant->where('archery_event_participants.age_category_id', $age_category_id);
        }

        $participants = $archery_event_participant->get();

        $category = ArcheryEventCategoryDetail::where("team_category_id", $team_category_id)
            ->where("distance_id", $distance_id)
            ->where("competition_category_id", $competition_category_id)
            ->where("age_category_id", $age_category_id)
            ->where("event_id", $event_id)
            ->first();

        if (!$category) {
            throw new BLoCException("CATEGORY NOT FOUND");
        }

        $participant_is_present = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.name",
            "archery_event_participant_members.have_shoot_off",
            "archery_event_participants.is_present"
        )->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_id', $event_id)
            ->where('archery_event_participants.event_category_id', $category->id)
            ->where("archery_event_participants.is_present", 1)
            ->get();

        $event_elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();

        $archery_event_score = [];

        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        foreach ($participants as $key => $value) {
            $score = $this->generateScoreBySession($value->id, $score_type, $session);
            $score["member"] = $value;
            $score["have_shoot_off"] = $value->have_shoot_off;
            $archery_event_score[] = $score;
        }

        usort($archery_event_score, function ($a, $b) {
            if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                    return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                }
                return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
            }
            return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
        });

        // cek apakah template telah di set atau belum
        if (!$event_elimination) {
            $elimination_template = $category->default_elimination_count;
            $newArray = [];
            $newValue = [];
            // cek apakah peserta yang is_preasent 1 lebih besar dari elimination template
            if ($elimination_template > 0 && $participant_is_present->count() > $elimination_template) {
                // cek apakah archer terakhir sesuai di yang sesuai template eliminasi udah melakukan shoot secara lengkap
                if ($archery_event_score[$elimination_template - 1]["sessions"][$category->session_in_qualification]["total"] > 0 && $archery_event_score[$elimination_template]["sessions"][$category->session_in_qualification]["total"] > 0) {
                    // cek apakah terdapat total point yang sama
                    if ($archery_event_score[$elimination_template - 1]["total"] === $archery_event_score[$elimination_template]["total"]) {
                        $total = $archery_event_score[$elimination_template - 1]["total"];
                        foreach ($archery_event_score as $key => $value) {
                            $member = ArcheryEventParticipantMember::find($value["member"]->id);
                            if (!$member) {
                                throw new BLoCException("member nan");
                            }

                            if ($value["member"]->is_present == 1) {
                                if ($value["total"] === $total) {
                                    $scooring_session_11_member = ArcheryScoring::where("scoring_session", 11)->where("participant_member_id", $member->id)->first();
                                    if (!$scooring_session_11_member) {
                                        $member->update(["have_shoot_off" => 1]);
                                    } else {
                                        if ($scooring_session_11_member->total == 0) {
                                            $member->update(["have_shoot_off" => 1]);
                                        } else {
                                            $member->update(["have_shoot_off" => 2]);
                                        }
                                    }
                                } else {
                                    $member->update(["have_shoot_off" => 0]);
                                }
                            } else {
                                $member->update(["have_shoot_off" => 0]);
                            }

                            $newValue = $this->generateScoreBySession($value["member"]->id, $score_type, $session);
                            $newValue["member"] = $value["member"];
                            $newValue["have_shoot_off"] = $member->have_shoot_off;
                            array_push($newArray, $newValue);
                        }
                        usort($newArray, function ($a, $b) {
                            if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                                if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                                    return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                                }
                                return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
                            }
                            return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
                        });
                        return $newArray;
                    } else {
                        foreach ($archery_event_score as $key => $value) {
                            $member = ArcheryEventParticipantMember::find($value["member"]->id);
                            if (!$member) {
                                throw new BLoCException("member nan");
                            }
                            $member->update(["have_shoot_off" => 0]);
                            $newValue = $this->generateScoreBySession($value["member"]->id, $score_type, $session);
                            $newValue["member"] = $value["member"];
                            $newValue["have_shoot_off"] = $member->have_shoot_off;
                            array_push($newArray, $newValue);
                        }
                        usort($newArray, function ($a, $b) {
                            if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                                if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                                    return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                                }
                                return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
                            }
                            return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
                        });
                        return $newArray;
                    }
                } else {
                    foreach ($archery_event_score as $key => $value) {
                        $member = ArcheryEventParticipantMember::find($value["member"]->id);
                        if (!$member) {
                            throw new BLoCException("member nan");
                        }
                        $member->update(["have_shoot_off" => 0]);
                        $newValue = $this->generateScoreBySession($value["member"]->id, $score_type, $session);
                        $newValue["member"] = $value["member"];
                        $newValue["have_shoot_off"] = $member->have_shoot_off;
                        array_push($newArray, $newValue);
                    }
                    usort($newArray, function ($a, $b) {
                        if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                            if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                                return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                            }
                            return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
                        }
                        return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
                    });
                    return $newArray;
                }
            } else {
                foreach ($archery_event_score as $key => $value) {
                    $member = ArcheryEventParticipantMember::find($value["member"]->id);
                    if (!$member) {
                        throw new BLoCException("member nan");
                    }

                    $scooring_session_11_member = ArcheryScoring::where("scoring_session", 11)->where("participant_member_id", $member->id)->first();

                    if ($scooring_session_11_member && $scooring_session_11_member->total == 0) {
                        $member->update(["have_shoot_off" => 0]);
                    }

                    if ($member->have_shoot_off === 1) {
                        $member->update(["have_shoot_off" => 0]);
                    }


                    $newValue = $this->generateScoreBySession($value["member"]->id, $score_type, $session);
                    $newValue["member"] = $value["member"];
                    $newValue["have_shoot_off"] = $member->have_shoot_off;
                    array_push($newArray, $newValue);
                }
                usort($newArray, function ($a, $b) {
                    if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                        if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                            return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                        }
                        return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
                    }
                    return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
                });
                return $newArray;
            }
        }

        return $archery_event_score;
    }

    protected function teamBestOfThree($individu_category_id, $individu_session_in_qualification, $team_category_id)
    {
        for ($i = 0; $i < $individu_session_in_qualification; $i++) {
            $session[] = $i + 1;
        }
        $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($individu_category_id, 1, $session, false, null, true);
        $session = [];
        $participant_club = [];
        $sequence_club = [];
        $participants = ArcheryEventParticipant::select("archery_event_participants.*", "archery_clubs.name as club_name")
            ->where("event_category_id", $team_category_id)
            ->where("status", 1)
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")
            ->get();
        foreach ($participants as $key => $value) {
            $club_members = [];
            $total_per_point = self::ArcheryScoringDetailPoint();
            $total = 0;
            $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
            foreach ($qualification_rank as $k => $member_rank) {
                if ($value->club_id == $member_rank["club_id"]) {
                    if ($member_rank["total"]  < 1) {
                        continue;
                    }
                    foreach ($member_rank["total_per_points"] as $p => $t) {
                        $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                    }
                    $total = $total + $member_rank["total"];
                    $club_members[] = $member_rank["member"];
                    unset($qualification_rank[$k]);
                }
                if (count($club_members) == 3)
                    break;
            }
            $participant_club[] = [
                "participant_id" => $value->id,
                "club_id" => $value->club_id,
                "club_name" => $value->club_name,
                "team" => $value->club_name . " " . $sequence_club[$value->club_id],
                "total" => $total,
                "total_x_plus_ten" => isset($total_per_point["x"]) ? $total_per_point["x"] + $total_per_point["10"] : 0,
                "total_x" => isset($total_per_point["x"]) ? $total_per_point["x"] : 0,
                "total_per_points" => $total_per_point,
                "total_tmp" => ArcheryScoring::getTotalTmp($total_per_point, $total),
                "teams" => $club_members
            ];
        }
        usort($participant_club, function ($a, $b) {
            return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
        });

        $new_array = [];
        foreach ($participant_club as $key => $value) {
            if (count($value["teams"]) == 3) {
                array_push($new_array, $value);
            }
        }
        return $new_array;
    }

    protected function mixTeamBestOfThree($category_detail)
    {
        for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }
        $category_detail_male = ArcheryEventCategoryDetail::where("event_id", $category_detail->event_id)
            ->where("age_category_id", $category_detail->age_category_id)
            ->where("competition_category_id", $category_detail->competition_category_id)
            ->where("distance_id", $category_detail->distance_id)
            ->where("team_category_id", "individu male")->first();
        $qualification_male = ArcheryScoring::getScoringRankByCategoryId($category_detail_male->id, 1, $session);

        $category_detail_female = ArcheryEventCategoryDetail::where("event_id", $category_detail->event_id)
            ->where("age_category_id", $category_detail->age_category_id)
            ->where("competition_category_id", $category_detail->competition_category_id)
            ->where("distance_id", $category_detail->distance_id)
            ->where("team_category_id", "individu female")->first();
        $qualification_female = ArcheryScoring::getScoringRankByCategoryId($category_detail_female->id, 1, $session);

        $participant_club = [];
        $sequence_club = [];
        $participants = ArcheryEventParticipant::select("archery_event_participants.*", "archery_clubs.name as club_name")->where("event_category_id", $category_detail->id)
            ->where("status", 1)
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")->get();
        foreach ($participants as $key => $value) {
            $club_members = [];
            $total_per_point = self::ArcheryScoringDetailPoint();
            $total = 0;
            $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
            foreach ($qualification_male as $k => $male_rank) {
                if ($value->club_id == $male_rank["club_id"]) {
                    if ($male_rank["total"]  < 1) {
                        continue;
                    }
                    foreach ($male_rank["total_per_points"] as $p => $t) {
                        $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                    }
                    $total = $total + $male_rank["total"];
                    $club_members[] = $male_rank["member"];
                    unset($qualification_male[$k]);
                }
                if (count($club_members) == 1)
                    break;
            }
            foreach ($qualification_female as $ky => $female_rank) {
                if ($value->club_id == $female_rank["club_id"]) {
                    if ($female_rank["total"]  < 1) {
                        continue;
                    }
                    foreach ($female_rank["total_per_points"] as $p => $t) {
                        $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                    }
                    $total = $total + $female_rank["total"];
                    $club_members[] = $female_rank["member"];
                    unset($qualification_female[$ky]);
                }
                if (count($club_members) == 2)
                    break;
            }

            $participant_club[] = [
                "participant_id" => $value->id,
                "club_id" => $value->club_id,
                "club_name" => $value->club_name,
                "team" => $value->club_name . " " . $sequence_club[$value->club_id],
                "total" => $total,
                "total_x_plus_ten" => isset($total_per_point["x"]) ? $total_per_point["x"] + $total_per_point["10"] : 0,
                "total_x" => isset($total_per_point["x"]) ? $total_per_point["x"] : 0,
                "total_per_points" => $total_per_point,
                "total_tmp" => ArcheryScoring::getTotalTmp($total_per_point, $total),
                "teams" => $club_members
            ];
        }
        usort($participant_club, function ($a, $b) {
            return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
        });

        $new_array = [];
        foreach ($participant_club as $key => $value) {
            if (count($value["teams"]) == 2) {
                array_push($new_array, $value);
            }
        }
        return $new_array;
    }

    protected function getScoringRankByCategoryIdForEliminationSelection($event_category_id, $score_type, array $sessions = [1, 2, 3, 4, 5], $orderByBudrestNumber = false, $name = null, $is_present = false)
    {
        $participants_query = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.have_shoot_off",
            "users.name",
            "archery_event_participant_members.user_id",
            "users.gender",
            "archery_event_participants.id as participant_id",
            "archery_event_participants.event_id",
            "archery_event_participants.is_present",
            "archery_clubs.name as club_name",
            "archery_clubs.id as club_id",
            "archery_event_qualification_schedule_full_day.bud_rest_number",
            "archery_event_qualification_schedule_full_day.target_face"
        )
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("users", "archery_event_participant_members.user_id", "=", "users.id")
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")
            ->leftJoin("archery_event_qualification_schedule_full_day", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_category_id', $event_category_id);

        if ($name) {
            $participants_query->whereRaw("users.name LIKE ?", ["%" . $name . "%"]);
        }


        if ($orderByBudrestNumber) {
            $participants_query->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number")
                ->orderBy("archery_event_qualification_schedule_full_day.target_face");
        }

        if ($is_present) {
            $participants_query->where("archery_event_participants.is_present", 1);
        }

        $participants_collection = $participants_query->get();
        $archery_event_score = [];
        foreach ($participants_collection as $key => $value) {
            $score = $this->generateScoreBySessionEliminationSelection($value->id, $score_type, $sessions);
            $score["club_id"] = $value->club_id;
            $score["club_name"] = $value->club_name;
            $score["member"] = $value;
            $score["have_shoot_off"] = $value->have_shoot_off;
            $score["member"]["participant_number"] = ArcheryEventParticipantNumber::getNumber($value->participant_id);
            $archery_event_score[] = $score;
        }

        if (!$orderByBudrestNumber) {
            usort($archery_event_score, function ($a, $b) {
                if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                    if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                        return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                    }
                    return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
                }
                return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
            });
        }

        return $archery_event_score;
    }

    protected function getScoringRankForEliminationSelection($distance_id, $team_category_id, $competition_category_id, $age_category_id, $gender, $score_type, $event_id)
    {
        $archery_event_participant = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.name",
            "archery_event_participant_members.gender",
            "archery_event_participant_members.have_shoot_off",
            "archery_clubs.name as club_name",
            "archery_event_qualification_schedule_full_day.bud_rest_number",
            "archery_event_qualification_schedule_full_day.target_face",
            "archery_event_participants.is_present"
        )->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")
            ->leftJoin("archery_event_qualification_schedule_full_day", "archery_event_participants.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_id', $event_id);
        if (!is_null($team_category_id)) {
            $archery_event_participant->where('archery_event_participants.team_category_id', $team_category_id);
        }
        if (!is_null($distance_id)) {
            $archery_event_participant->where('archery_event_participants.distance_id', $distance_id);
        }
        if (!is_null($gender) && !empty($gender)) {
            $archery_event_participant->where('archery_event_participant_members.gender', $gender);
        }
        if (!is_null($competition_category_id)) {
            $archery_event_participant->where('archery_event_participants.competition_category_id', $competition_category_id);
        }
        if (!is_null($age_category_id)) {
            $archery_event_participant->where('archery_event_participants.age_category_id', $age_category_id);
        }

        $participants = $archery_event_participant->get();

        $category = ArcheryEventCategoryDetail::where("team_category_id", $team_category_id)
            ->where("distance_id", $distance_id)
            ->where("competition_category_id", $competition_category_id)
            ->where("age_category_id", $age_category_id)
            ->where("event_id", $event_id)
            ->first();

        if (!$category) {
            throw new BLoCException("CATEGORY NOT FOUND");
        }

        $participant_is_present = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.name",
            "archery_event_participant_members.have_shoot_off",
            "archery_event_participants.is_present"
        )->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_id', $event_id)
            ->where('archery_event_participants.event_category_id', $category->id)
            ->where("archery_event_participants.is_present", 1)
            ->get();

        $event_elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();

        $archery_event_score = [];

        $session = [];
        for ($i = 0; $i < env('COUNT_STAGE_ELIMINATION_SELECTION'); $i++) {
            $session[] = $i + 1;
        }

        foreach ($participants as $key => $value) {
            $score = $this->generateScoreBySessionEliminationSelection($value->id, $score_type, $session);
            $score["member"] = $value;
            $score["have_shoot_off"] = $value->have_shoot_off;
            $archery_event_score[] = $score;
        }

        usort($archery_event_score, function ($a, $b) {
            if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                    return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                }
                return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
            }
            return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
        });

        return $archery_event_score;
    }

    protected function generateScoreBySessionEliminationSelection(int $participant_member_id, int $type, array $filter_session = [1, 2, 3, 4, 5])
    {
        $total_per_points = [
            "" => 0,
            "1" => 0,
            "2" => 0,
            "3" => 0,
            "4" => 0,
            "5" => 0,
            "6" => 0,
            "7" => 0,
            "8" => 0,
            "9" => 0,
            "10" => 0,
            "x" => 0,
            "m" => 0,
        ];
        $member_scors = $this->where("participant_member_id", $participant_member_id)
            ->whereIn("scoring_session", $filter_session)
            ->where("type", $type)
            ->get();
        $sessions = [];
        foreach ($filter_session as $s) {
            $sessions[$s] = array(
                "score" => [],
                "total_per_point" => $total_per_points,
                "total" => 0,
                "total_tmp" => 0,
                "session" => $s,
                "total_x" => 0,
                "total_x_plus_ten" => 0
            );
        }
        $total = 0;
        $total_tmp = 0;
        $count_shot_arrows = 0;
        foreach ($member_scors as $k => $score) {
            $score_detail = json_decode($score->scoring_detail);
            $score_rambahan = [];
            $total_per_session = 0;
            foreach ($score_detail as $ks => $sd) {
                $get_score = [];
                foreach ($sd as $rambahan => $arrows) {
                    if ($arrows->id == "") {
                        continue;
                    } else {
                        $count_shot_arrows += 1;
                    }
                    $get_score[] = $arrows->id;
                    $total = $total + $arrows->value;
                    $total_per_session = $total_per_session + $arrows->value;
                    $total_per_points[$arrows->id] = $total_per_points[$arrows->id] + 1;
                    $sessions[$score->scoring_session]["total_per_point"][$arrows->id] = $sessions[$score->scoring_session]["total_per_point"][$arrows->id] + 1;
                }
                $score_rambahan[$ks] = $get_score;
            }
            $sessions[$score->scoring_session]["total_tmp"] = $this->getTotalTmp($sessions[$score->scoring_session]["total_per_point"], $total_per_session);
            $sessions[$score->scoring_session]["score"] = $score_rambahan;
            $sessions[$score->scoring_session]["total"] = $total_per_session;
            $sessions[$score->scoring_session]["scoring_id"] = $score->id;
            $sessions[$score->scoring_session]["total_x"] = $sessions[$score->scoring_session]["total_per_point"]["x"];
            $sessions[$score->scoring_session]["total_x_plus_ten"] = $sessions[$score->scoring_session]["total_per_point"]["x"] + $sessions[$score->scoring_session]["total_per_point"]["10"];
        }

        // cek apakah member tersebut melakukan shot off atau tidak
        $total_shot_off = 0;
        $total_distance_from_x = 0;
        $shot_off = ArcheryScoring::where("scoring_session", 11)->where("participant_member_id", $participant_member_id)->first();
        if ($shot_off) {
            $total_shot_off = $shot_off->total;
            $scoring_shoot_off_detail = json_decode($shot_off->scoring_detail);
            foreach ($scoring_shoot_off_detail as $key => $value) {
                $distance_from_x = $value->distance_from_x;
                if (gettype($value->distance_from_x) == "string") {
                    $distance_from_x = 0;
                }
                $total_distance_from_x = $total_distance_from_x + $distance_from_x;
            }
            $sessions["11"] = $scoring_shoot_off_detail;
        }

        $participant = ArcheryEventParticipantMember::select("archery_event_participants.*")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->where("archery_event_participant_members.id", $participant_member_id)
            ->first();

        if (!$participant) {
            throw new BLoCException("PARTICIPANT TIDAK ADA");
        }

        $total_fix = $total + $total_shot_off;

        $category_detail = ArcheryEventCategoryDetail::where('id', $participant->event_category_id)->first();
        $total_arrow = (env('COUNT_SHOT_IN_STAGE_ELIMINATION_SELECTION') * env('COUNT_STAGE_ELIMINATION_SELECTION')) * env('COUNT_STAGE_ELIMINATION_SELECTION');
        $total_irat = $count_shot_arrows == 0 ? 0 : round(($total / $count_shot_arrows), 3);

        $output = [
            "sessions" => $sessions,
            "total_shot_off" => $participant->is_present == 1 ? $total_shot_off : 0,
            "total_distance_from_x" => $participant->is_present == 1 ? $total_distance_from_x : 0,
            "total" => $total,
            "total_x" => $total_per_points["x"],
            "total_per_points" => $total_per_points,
            "total_x_plus_ten" => $total_per_points["x"] + $total_per_points["10"],
            "total_tmp" => $participant->is_present == 1 ? $this->getTotalTmp($total_per_points, $total) : 0,
            "total_arrow" => $count_shot_arrows,
            "total_irat" => $total_irat
        ];
        return $output;
    }

    // All result of qualification & elimination to get total irat for event selection
    protected function getScoringRankByCategoryIdForEventSelection($event_category_id, array $session_qualification = [1, 2], array $session_elimination = [1, 2, 3, 4, 5], $orderByBudrestNumber = false, $name = null, $is_present = false)
    {
        $participants_query = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.have_shoot_off",
            "users.name",
            "archery_event_participant_members.user_id",
            "users.gender",
            "archery_event_participants.id as participant_id",
            "archery_event_participants.event_id",
            "archery_event_participants.is_present",
            "archery_clubs.name as club_name",
            "archery_clubs.id as club_id",
            "archery_event_qualification_schedule_full_day.bud_rest_number",
            "archery_event_qualification_schedule_full_day.target_face"
        )
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("users", "archery_event_participant_members.user_id", "=", "users.id")
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")
            ->leftJoin("archery_event_qualification_schedule_full_day", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_category_id', $event_category_id);

        if ($name) {
            $participants_query->whereRaw("users.name LIKE ?", ["%" . $name . "%"]);
        }


        if ($orderByBudrestNumber) {
            $participants_query->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number")
                ->orderBy("archery_event_qualification_schedule_full_day.target_face");
        }

        if ($is_present) {
            $participants_query->where("archery_event_participants.is_present", 1);
        }

        $participants_collection = $participants_query->get();
        $archery_event_score = [];
        foreach ($participants_collection as $key => $value) {
            $score_qualification = $this->generateScoreBySession($value->id, 3, $session_qualification);
            $score_elimination = $this->generateScoreBySessionEliminationSelection($value->id, 4, $session_elimination);
            $score["qualification"] = $score_qualification;
            $score["elimination"] = $score_elimination;
            $score["club_id"] = $value->club_id;
            $score["club_name"] = $value->club_name;
            $score["member"] = $value;
            $score["have_shoot_off"] = $value->have_shoot_off;
            $score["all_total_irat"] = $score_qualification['total_irat'] + $score_elimination['total_irat'];
            $score["member"]["participant_number"] = ArcheryEventParticipantNumber::getNumber($value->participant_id);
            $archery_event_score[] = $score;
        }

        if (!$orderByBudrestNumber) {
            usort($archery_event_score, function ($a, $b) {
                if ($a["have_shoot_off"] != 0 && $b["have_shoot_off"] != 0) {
                    if ($a["total_shot_off"] != 0 && $b["total_shot_off"] != 0 && $a["total_shot_off"] == $b["total_shot_off"]) {
                        return $b["total_distance_from_x"] < $a["total_distance_from_x"] ? 1 : -1;
                    }
                    return $b["total_shot_off"] > $a["total_shot_off"] ? 1 : -1;
                }
                return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
            });
        }

        return $archery_event_score;
    }
}
