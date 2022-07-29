<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryScoringEliminationGroup extends Model
{
    protected $table = 'archery_scoring_elimination_group';
    protected $guarded = ["id"];

    protected static $score_value = [
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
    protected static $elimination_scores_format_by_type = [
        "1" => [
            "shot" => [
                ["score" => ["", "", "", "", "", ""], "total" => 0, "status" => "empty", "point" => 0], // status = ["empty","win","draw","lose"]
                ["score" => ["", "", "", "", "", ""], "total" => 0, "status" => "empty", "point" => 0],
                ["score" => ["", "", "", "", "", ""], "total" => 0, "status" => "empty", "point" => 0],
                ["score" => ["", "", "", "", "", ""], "total" => 0, "status" => "empty", "point" => 0],
                ["score" => ["", "", "", "", "", ""], "total" => 0, "status" => "empty", "point" => 0]
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
                ["score" => ["", "", "", "", "", ""], "total" => 0],
                ["score" => ["", "", "", "", "", ""], "total" => 0],
                ["score" => ["", "", "", "", "", ""], "total" => 0],
                ["score" => ["", "", "", "", "", ""], "total" => 0],
                ["score" => ["", "", "", "", "", ""], "total" => 0]
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

    public static function makeEliminationScoringTypePointFormat()
    {
        $scores = self::$elimination_scores_format_by_type[1];
        return $scores;
    }

    public static function makeEliminationScoringTypeTotalFormat()
    {
        $scores = self::$elimination_scores_format_by_type[2];
        return $scores;
    }

    public static function calculateEliminationScoringTypeTotalFormat(array $scoring_1, array $scoring_2, $save_permanent)
    {
        $scores = self::$elimination_scores_format_by_type[2];
        $total_score_1 = 0;
        $total_score_2 = 0;
        $win_1 = 0;
        $win_2 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;
            $scoring_2_total_score_per_rambahan = 0;
            foreach ($shot["score"] as $i => $s) {
                $s1 = self::$score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
                $s2 = self::$score_value[$scoring_2["scores"]["shot"][$k]["score"][$i]];
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
                $es_score_1 = $scoring_1["scores"]["extra_shot"][$es]["score"] != "x" ? self::$score_value[$scoring_1["scores"]["extra_shot"][$es]["score"]] : 11;
                $es_score_2 = $scoring_2["scores"]["extra_shot"][$es]["score"] != "x" ? self::$score_value[$scoring_2["scores"]["extra_shot"][$es]["score"]] : 11;
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
            $scoring_1["participant_id"] => $scoring_1,
            $scoring_2["participant_id"] => $scoring_2,
        ];
    }

    public static function calculateEliminationScoringTypeTotalFormatBye(array $scoring_1)
    {
        $scores = self::$elimination_scores_format_by_type[2];
        $total_score_1 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;
            foreach ($shot["score"] as $i => $s) {
                $s1 = self::$score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
            }

            $total_score_1 = $total_score_1 + $scoring_1_total_score_per_rambahan;
            $scoring_1["scores"]["shot"][$k]["total"] = $scoring_1_total_score_per_rambahan;
        }


        $scoring_1["scores"]["total"] = $total_score_1;


        $scoring_1["scores"]["result"] = $total_score_1;


        $scoring_1["scores"]["eliminationt_score_type"] = 2;

        return [
            $scoring_1["participant_id"] => $scoring_1,
        ];
    }

    public static function calculateEliminationScoringTypePointFormat(array $scoring_1, array $scoring_2, $save_permanent)
    {
        $scores = self::$elimination_scores_format_by_type[1];
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
                $s1 = self::$score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
                $s2 = self::$score_value[$scoring_2["scores"]["shot"][$k]["score"][$i]];
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
                $es_score_1 = $scoring_1["scores"]["extra_shot"][$es]["score"] != "x" ? self::$score_value[$scoring_1["scores"]["extra_shot"][$es]["score"]] : 11;
                $es_score_2 = $scoring_2["scores"]["extra_shot"][$es]["score"] != "x" ? self::$score_value[$scoring_2["scores"]["extra_shot"][$es]["score"]] : 11;
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
            $scoring_1["participant_id"] => $scoring_1,
            $scoring_2["participant_id"] => $scoring_2,
        ];
    }

    public static function calculateEliminationScoringTypePointFormatbye(array $scoring_1)
    {
        $scores = self::$elimination_scores_format_by_type[1];
        $total_point_1 = 0;
        $total_score_1 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;
            $point_1_per_rambahan = 0;
            foreach ($shot["score"] as $i => $s) {
                $s1 = self::$score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
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
            $scoring_1["participant_id"] => $scoring_1,
        ];
    }
}
