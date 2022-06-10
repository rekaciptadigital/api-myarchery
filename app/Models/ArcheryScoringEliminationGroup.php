<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryScoringEliminationGroup extends Model
{
    protected $table = 'archery_scoring_elimination_group';
    protected $guarded = ["id"];

    protected static $elimination_scores_format_by_type = [
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
}
