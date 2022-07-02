<?php

namespace App\Libraries;

use DAI\Utils\Exceptions\BLoCException;

class EliminationFormat
{
    static $match_potition = [
        "64" => [
            [1, 64],
            [33, 32],
            [17, 48],
            [49, 16],
            [9, 56],
            [41, 24],
            [25, 40],
            [57, 8],
            [5, 60],
            [37, 28],
            [21, 44],
            [53, 12],
            [13, 52],
            [45, 20],
            [29, 36],
            [61, 4],
            [3, 62],
            [35, 30],
            [19, 46],
            [51, 14],
            [11, 54],
            [43, 22],
            [27, 38],
            [59, 6],
            [7, 58],
            [39, 26],
            [23, 42],
            [55, 10],
            [15, 50],
            [47, 18],
            [31, 34],
            [63, 2],
        ],
        "32" => [
            [1, 32],
            [17, 16],
            [9, 24],
            [25, 8],
            [5, 28],
            [21, 12],
            [13, 20],
            [29, 4],
            [3, 30],
            [19, 14],
            [11, 22],
            [27, 6],
            [7, 26],
            [23, 10],
            [15, 18],
            [31, 2],
        ],
        "16" => [
            [1, 16],
            [9, 8],
            [5, 12],
            [13, 4],
            [3, 14],
            [11, 6],
            [7, 10],
            [15, 2],
        ],
        "8" => [
            [1, 8],
            [5, 4],
            [3, 6],
            [7, 2]
        ],

        "4" => [
            [1, 4],
            [3, 2]
        ]
    ];

    static $next_match_potition = [
        // eliminasi template 32
        "32" => [
            "1" => [
                "1-1" => [2, 1, 0], // round, match, index
                "2-1" => [2, 1, 1], // round, match, index
                "3-1" => [2, 2, 0], // round, match, index
                "4-1" => [2, 2, 1], // round, match, index
                "5-1" => [2, 3, 0], // round, match, index
                "6-1" => [2, 3, 1], // round, match, index
                "7-1" => [2, 4, 0], // round, match, index
                "8-1" => [2, 4, 1], // round, match, index
                "9-1" => [2, 5, 0], // round, match, index
                "10-1" => [2, 5, 1], // round, match, index
                "11-1" => [2, 6, 0], // round, match, index
                "12-1" => [2, 6, 1], // round, match, index
                "13-1" => [2, 7, 0], // round, match, index
                "14-1" => [2, 7, 1], // round, match, index
                "15-1" => [2, 8, 0], // round, match, index
                "16-1" => [2, 8, 1], // round, match, index
            ],
            "2" => [
                "1-1" => [3, 1, 0], // round, match, index
                "2-1" => [3, 1, 1], // round, match, index
                "3-1" => [3, 2, 0], // round, match, index
                "4-1" => [3, 2, 1], // round, match, index
                "5-1" => [3, 3, 0], // round, match, index
                "6-1" => [3, 3, 1], // round, match, index
                "7-1" => [3, 4, 0], // round, match, index
                "8-1" => [3, 4, 1], // round, match, index
            ],
            "3" => [
                "1-1" => [4, 1, 0], // round, match, index
                "2-1" => [4, 1, 1], // round, match, index
                "3-1" => [4, 2, 0], // round, match, index
                "4-1" => [4, 2, 1], // round, match, index
            ],
            "4" => [
                "1-1" => [5, 1, 0], // round, match, index
                "2-1" => [5, 1, 1], // round, match, index
                "1-0" => [6, 1, 0], // round, match, index
                "2-0" => [6, 1, 1], // round, match, index
            ]
        ],

        // eliminasi template babak 16 besar
        "16" => [
            "1" => [
                "1-1" => [2, 1, 0], // round, match, index
                "2-1" => [2, 1, 1], // round, match, index
                "3-1" => [2, 2, 0], // round, match, index
                "4-1" => [2, 2, 1], // round, match, index
                "5-1" => [2, 3, 0], // round, match, index
                "6-1" => [2, 3, 1], // round, match, index
                "7-1" => [2, 4, 0], // round, match, index
                "8-1" => [2, 4, 1], // round, match, index
            ],
            "2" => [
                "1-1" => [3, 1, 0], // round, match, index
                "2-1" => [3, 1, 1], // round, match, index
                "3-1" => [3, 2, 0], // round, match, index
                "4-1" => [3, 2, 1], // round, match, index
            ],
            "3" => [
                "1-1" => [4, 1, 0], // round, match, index
                "2-1" => [4, 1, 1], // round, match, index
                "1-0" => [5, 1, 0], // round, match, index
                "2-0" => [5, 1, 1], // round, match, index
            ]
        ],

        // eliminasi template babak 8 besar
        "8" => [
            "1" => [
                "1-1" => [2, 1, 0], // round, match, index
                "2-1" => [2, 1, 1], // round, match, index
                "3-1" => [2, 2, 0], // round, match, index
                "4-1" => [2, 2, 1], // round, match, index
            ],
            "2" => [
                "1-1" => [3, 1, 0], // round, match, index
                "2-1" => [3, 1, 1], // round, match, index
                "1-0" => [4, 1, 0], // round, match, index
                "2-0" => [4, 1, 1], // round, match, index
            ]
        ],

        // eliminasi template babak 4 besar
        "4" => [
            "1" => [
                "1-1" => [2, 1, 0],
                "2-1" => [2, 1, 1],
                "1-0" => [3, 1, 0],
                "2-0" => [3, 1, 1]
            ]
        ]
    ];

    static $elimination_champion = [
        "32" => [
            // round 1
            "1-1-0" => 17,
            "1-2-0" => 18,
            "1-3-0" => 19,
            "1-4-0" => 20,
            "1-5-0" => 21,
            "1-6-0" => 22,
            "1-7-0" => 23,
            "1-8-0" => 24,
            "1-9-0" => 25,
            "1-10-0" => 26,
            "1-11-0" => 27,
            "1-12-0" => 28,
            "1-13-0" => 29,
            "1-14-0" => 30,
            "1-15-0" => 31,
            "1-16-0" => 32,

            // round 2
            "2-1-0" => 9,
            "2-2-0" => 10,
            "2-3-0" => 11,
            "2-4-0" => 12,
            "2-5-0" => 13,
            "2-6-0" => 14,
            "2-7-0" => 15,
            "2-8-0" => 16,

            //round 3
            "3-1-0" => 5,
            "3-2-0" => 6,
            "3-3-0" => 7,
            "3-4-0" => 8,

            // final
            "5-1-1" => 1,
            "5-1-0" => 2,

            // perebutan juara 3
            "6-1-1" => 3,
            "6-1-0" => 4
        ],

        "16" => [
            // round 1
            "1-1-0" => 9,
            "1-2-0" => 10,
            "1-3-0" => 11,
            "1-4-0" => 12,
            "1-5-0" => 13,
            "1-6-0" => 14,
            "1-7-0" => 15,
            "1-8-0" => 16,

            // round 2
            "2-1-0" => 5,
            "2-2-0" => 6,
            "2-3-0" => 7,
            "2-4-0" => 8,

            // final
            "4-1-1" => 1,
            "4-1-0" => 2,

            // perebutan juara 3
            "5-1-1" => 3,
            "5-1-0" => 4,
        ],
        "8" => [
            // round 1
            "1-1-0" => 5,
            "1-2-0" => 6,
            "1-3-0" => 7,
            "1-4-0" => 8,

            // final
            "3-1-1" => 1,
            "3-1-0" => 2,

            // rebut juara 3
            "4-1-1" => 3,
            "4-1-0" => 4
        ],
        "4" => [
            // final
            "2-1-1" => 1,
            "2-1-0" => 2,

            // rebut juara 3
            "3-1-1" => 3,
            "3-1-0" => 4
        ]
    ];


    public static function Template16($members = [])
    {
        $matches = [];
        foreach ($members as $key => $value) {
            $m = [];
            foreach ($value as $key => $value) {
                $m[] = $value;
            }
            $matches[] = ["round" => "", "seeds" => $m];
        }

        return $matches;
    }

    public static function Template8($members = [])
    {
        $matches = [];
        foreach ($members as $key => $value) {
            $m = [];
            foreach ($value as $key => $value) {
                $m[] = $value;
            }
            $matches[] = ["round" => "", "seeds" => $m];
        }

        return $matches;
    }

    public static function Template32($members = [])
    {
        $matches = [];
        foreach ($members as $key => $value) {
            $m = [];
            foreach ($value as $key => $value) {
                $m[] = $value;
            }
            $matches[] = ["round" => "", "seeds" => $m];
        }

        return $matches;
    }

    public static function Template4($members = [])
    {
        $matches = [];
        foreach ($members as $key => $value) {
            $m = [];
            foreach ($value as $key => $value) {
                $m[] = $value;
            }
            $matches[] = ["round" => "", "seeds" => $m];
        }

        return $matches;
    }

    public static function EliminationChampion($count_member_match, $round, $match, $win)
    {
        return isset(self::$elimination_champion[$count_member_match][$round . "-" . $match . "-" . $win]) ? self::$elimination_champion[$count_member_match][$round . "-" . $match . "-" . $win] : 0;
    }

    public static function NextMatch($count_member_match, $round, $match, $win)
    {
        if (!isset(self::$next_match_potition[$count_member_match][$round][$match . "-" . $win]))
            return [];

        $next = self::$next_match_potition[$count_member_match][$round][$match . "-" . $win];

        return [
            "round" => $next[0],
            "match" => $next[1],
            "index" => $next[2],
        ];
    }

    public static function MakeTemplate32($members = [])
    {
        $elimination_member_count = 32;
        $members_coll = [];
        $members = array_slice($members, 0, $elimination_member_count);
        // error_log(\json_encode($members_coll[1]["member"]));
        for ($i = 0; $i < $elimination_member_count; $i++) {
            if (isset($members[$i]["member"])) {
                $arr = collect($members[$i]["member"]);
                $arr["postition"] = $i + 1;
                $arr["win"] = 0;
                $members_coll[$i] = $arr;
            } else
                $members_coll[$i] = [];
        }
        $teams = [];
        foreach (self::$match_potition[$elimination_member_count] as $key => $value) {
            $team = [];
            foreach ($value as $k => $v) {
                $i = $v - 1;
                $team[] = isset($members_coll[$i]) ? $members_coll[$i] : [];
            }
            $teams[] = $team;
        }

        $teams_2[0] = [[], []];
        $teams_2[1] = [[], []];
        $teams_2[2] = [[], []];
        $teams_2[3] = [[], []];
        $teams_2[4] = [[], []];
        $teams_2[5] = [[], []];
        $teams_2[6] = [[], []];
        $teams_2[7] = [[], []];

        $teams_3[0] = [[], []];
        $teams_3[1] = [[], []];
        $teams_3[2] = [[], []];
        $teams_3[3] = [[], []];

        $teams_4[0] = [[], []];
        $teams_4[1] = [[], []];

        $teams_5[0] = [[], []];

        $teams_6[0] = [[], []];

        // round 1 match 1
        if (isset($teams[0][0]["id"]) && !isset($teams[0][1]["id"])) {
            $teams_2[0][0] = collect($teams[0][0]);
            $teams[0][0]["win"] = 1;
            $teams_2[0][0]["win"] = 0;
        }
        if (isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])) {
            $teams_2[0][0]["status"] = "wait";
        }
        if (!isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])) {
            $teams[0][1]["win"] = 1;
            $teams_2[0][0] = collect($teams[0][1]);
            $teams_2[0][0]["win"] = 0;
        }

        // round 1 match 2
        if (isset($teams[1][0]["id"]) && !isset($teams[1][1]["id"])) {
            $teams[1][0]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][0]);
            $teams_2[0][1]["win"] = 0;
        }
        if (!isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])) {
            $teams[1][1]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][1]);
            $teams_2[0][1]["win"] = 0;
        }
        if (isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])) {
            $teams_2[0][1]["status"] = "wait";
        }

        // round 1 match 3
        if (isset($teams[2][0]["id"]) && !isset($teams[2][1]["id"])) {
            $teams[2][0]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][0]);
            $teams_2[1][0]["win"] = 0;
        }
        if (!isset($teams[2][0]["id"]) && isset($teams[2][1]["id"])) {
            $teams[2][1]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][1]);
            $teams_2[1][0]["win"] = 0;
        }
        if (isset($teams[2][0]["id"]) && isset($teams[2][1]["id"])) {
            $teams_2[1][0]["status"] = "wait";
        }

        // round 1 match 4
        if (isset($teams[3][0]["id"]) && !isset($teams[3][1]["id"])) {
            $teams[3][0]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][0]);
        }
        if (!isset($teams[3][0]["id"]) && isset($teams[3][1]["id"])) {
            $teams[3][1]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][1]);
            $teams_2[1][1]["win"] = 0;
        }
        if (isset($teams[3][0]["id"]) && isset($teams[3][1]["id"])) {
            $teams_2[1][1]["status"] = "wait";
        }

        // round 1 match 5
        if (isset($teams[4][0]["id"]) && !isset($teams[4][1]["id"])) {
            $teams[4][0]["win"] = 1;
            $teams_2[2][0] = collect($teams[4][0]);
            $teams_2[2][0]["win"] = 0;
        }
        if (!isset($teams[4][0]["id"]) && isset($teams[4][1]["id"])) {
            $teams[4][1]["win"] = 1;
            $teams_2[2][0] = collect($teams[4][1]);
            $teams_2[2][0]["win"] = 0;
        }
        if (isset($teams[4][0]["id"]) && isset($teams[4][1]["id"])) {
            $teams_2[2][0]["status"] = "wait";
        }

        // round 1 match 6
        if (isset($teams[5][0]["id"]) && !isset($teams[5][1]["id"])) {
            $teams[5][0]["win"] = 1;
            $teams_2[2][1] = collect($teams[5][0]);
            $teams_2[2][1]["win"] = 0;
        }
        if (!isset($teams[5][0]["id"]) && isset($teams[5][1]["id"])) {
            $teams[5][1]["win"] = 1;
            $teams_2[2][1] = collect($teams[5][1]);
            $teams_2[2][1]["win"] = 0;
        }
        if (isset($teams[5][0]["id"]) && isset($teams[5][1]["id"])) {
            $teams_2[2][1]["status"] = "wait";
        }

        // round 1 match 7
        if (isset($teams[6][0]["id"]) && !isset($teams[6][1]["id"])) {
            $teams[6][0]["win"] = 1;
            $teams_2[3][0] = collect($teams[6][0]);
            $teams_2[3][0]["win"] = 0;
        }
        if (!isset($teams[6][0]["id"]) && isset($teams[6][1]["id"])) {
            $teams[6][1]["win"] = 1;
            $teams_2[3][0] = collect($teams[6][1]);
            $teams_2[3][0]["win"] = 0;
        }
        if (isset($teams[6][0]["id"]) && isset($teams[6][1]["id"])) {
            $teams_2[3][0]["status"] = "wait";
        }

        // round 1 match 8
        if (isset($teams[7][0]["id"]) && !isset($teams[7][1]["id"])) {
            $teams[7][0]["win"] = 1;
            $teams_2[3][1] = collect($teams[7][0]);
            $teams_2[3][1]["win"] = 0;
        }
        if (!isset($teams[7][0]["id"]) && isset($teams[7][1]["id"])) {
            $teams[7][1]["win"] = 1;
            $teams_2[3][1] = collect($teams[7][1]);
            $teams_2[3][1]["win"] = 0;
        }
        if (isset($teams[7][0]["id"]) && isset($teams[7][1]["id"])) {
            $teams_2[3][1]["status"] = "wait";
        }

        // round 1 match 9
        if (isset($teams[8][0]["id"]) && !isset($teams[8][1]["id"])) {
            $teams[8][0]["win"] = 1;
            $teams_2[4][0] = collect($teams[8][0]);
            $teams_2[4][0]["win"] = 0;
        }
        if (!isset($teams[8][0]["id"]) && isset($teams[8][1]["id"])) {
            $teams[8][1]["win"] = 1;
            $teams_2[4][0] = collect($teams[8][1]);
            $teams_2[4][0]["win"] = 0;
        }
        if (isset($teams[8][0]["id"]) && isset($teams[8][1]["id"])) {
            $teams_2[4][0]["status"] = "wait";
        }

        // round 1 match 10
        if (isset($teams[9][0]["id"]) && !isset($teams[9][1]["id"])) {
            $teams[9][0]["win"] = 1;
            $teams_2[4][1] = collect($teams[9][0]);
            $teams_2[4][1]["win"] = 0;
        }
        if (!isset($teams[9][0]["id"]) && isset($teams[9][1]["id"])) {
            $teams[9][1]["win"] = 1;
            $teams_2[4][1] = collect($teams[9][1]);
            $teams_2[4][1]["win"] = 0;
        }
        if (isset($teams[9][0]["id"]) && isset($teams[9][1]["id"])) {
            $teams_2[4][1]["status"] = "wait";
        }

        // round 1 match 11
        if (isset($teams[10][0]["id"]) && !isset($teams[10][1]["id"])) {
            $teams[10][0]["win"] = 1;
            $teams_2[5][0] = collect($teams[10][0]);
            $teams_2[5][0]["win"] = 0;
        }
        if (!isset($teams[10][0]["id"]) && isset($teams[10][1]["id"])) {
            $teams[10][1]["win"] = 1;
            $teams_2[5][0] = collect($teams[10][1]);
            $teams_2[5][0]["win"] = 0;
        }
        if (isset($teams[10][0]["id"]) && isset($teams[10][1]["id"])) {
            $teams_2[5][0]["status"] = "wait";
        }

        // round 1 match 12
        if (isset($teams[11][0]["id"]) && !isset($teams[11][1]["id"])) {
            $teams[11][0]["win"] = 1;
            $teams_2[5][1] = collect($teams[11][0]);
            $teams_2[5][1]["win"] = 0;
        }
        if (!isset($teams[11][0]["id"]) && isset($teams[11][1]["id"])) {
            $teams[11][1]["win"] = 1;
            $teams_2[5][1] = collect($teams[11][1]);
            $teams_2[5][1]["win"] = 0;
        }
        if (isset($teams[11][0]["id"]) && isset($teams[11][1]["id"])) {
            $teams_2[5][1]["status"] = "wait";
        }

        // round 1 match 13
        if (isset($teams[12][0]["id"]) && !isset($teams[12][1]["id"])) {
            $teams[12][0]["win"] = 1;
            $teams_2[6][0] = collect($teams[12][0]);
            $teams_2[6][0]["win"] = 0;
        }
        if (!isset($teams[12][0]["id"]) && isset($teams[12][1]["id"])) {
            $teams[12][1]["win"] = 1;
            $teams_2[6][0] = collect($teams[12][1]);
            $teams_2[6][0]["win"] = 0;
        }
        if (isset($teams[12][0]["id"]) && isset($teams[12][1]["id"])) {
            $teams_2[6][0]["status"] = "wait";
        }

        // round 1 match 14
        if (isset($teams[13][0]["id"]) && !isset($teams[13][1]["id"])) {
            $teams[13][0]["win"] = 1;
            $teams_2[6][1] = collect($teams[13][0]);
            $teams_2[6][1]["win"] = 0;
        }
        if (!isset($teams[13][0]["id"]) && isset($teams[13][1]["id"])) {
            $teams[13][1]["win"] = 1;
            $teams_2[6][1] = collect($teams[13][1]);
            $teams_2[6][1]["win"] = 0;
        }
        if (isset($teams[13][0]["id"]) && isset($teams[13][1]["id"])) {
            $teams_2[6][1]["status"] = "wait";
        }

        // round 1 match 15
        if (isset($teams[14][0]["id"]) && !isset($teams[14][1]["id"])) {
            $teams[14][0]["win"] = 1;
            $teams_2[7][0] = collect($teams[14][0]);
            $teams_2[7][0]["win"] = 0;
        }
        if (!isset($teams[14][0]["id"]) && isset($teams[14][1]["id"])) {
            $teams[14][1]["win"] = 1;
            $teams_2[7][0] = collect($teams[14][1]);
            $teams_2[7][0]["win"] = 0;
        }
        if (isset($teams[14][0]["id"]) && isset($teams[14][1]["id"])) {
            $teams_2[7][0]["status"] = "wait";
        }

        // round 1 match 16
        if (isset($teams[15][0]["id"]) && !isset($teams[15][1]["id"])) {
            $teams[15][0]["win"] = 1;
            $teams_2[7][1] = collect($teams[15][0]);
            $teams_2[7][1]["win"] = 0;
        }
        if (!isset($teams[15][0]["id"]) && isset($teams[15][1]["id"])) {
            $teams[15][1]["win"] = 1;
            $teams_2[7][1] = collect($teams[15][1]);
            $teams_2[7][1]["win"] = 0;
        }
        if (isset($teams[15][0]["id"]) && isset($teams[15][1]["id"])) {
            $teams_2[7][1]["status"] = "wait";
        }

        // round 2 match 1
        if (isset($teams_2[0][0]["id"]) && !isset($teams_2[0][1]["id"]) && !isset($teams_2[0][1]["status"])) {
            $teams_2[0][0]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][0]);
            $teams_3[0][0]["win"] = 0;
        }
        if (!isset($teams_2[0][0]["status"]) && !isset($teams_2[0][0]["id"]) && isset($teams_2[0][1]["id"])) {
            $teams_2[0][1]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][1]);
            $teams_3[0][0]["win"] = 0;
        }
        if (isset($teams_2[0][0]["id"]) && isset($teams_2[0][1]["id"])) {
            $teams_3[0][0]["status"] = "wait";
        }

        // round 2 match 2
        if (isset($teams_2[1][0]["id"]) && !isset($teams_2[1][1]["id"]) && !isset($teams_2[1][1]["status"])) {
            $teams_2[1][0]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][0]);
            $teams_3[0][1]["win"] = 0;
        }
        if (!isset($teams_2[1][0]["status"]) && !isset($teams_2[1][0]["id"]) && isset($teams_2[1][1]["id"])) {
            $teams_2[1][1]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][1]);
            $teams_3[0][1]["win"] = 0;
        }
        if (isset($teams_2[1][0]["id"]) && isset($teams_2[1][1]["id"])) {
            $teams_3[0][1]["status"] = "wait";
        }

        // round 2 match 3
        if (isset($teams_2[2][0]["id"]) && !isset($teams_2[2][1]["id"]) && !isset($teams_2[2][1]["status"])) {
            $teams_2[2][0]["win"] = 1;
            $teams_3[1][0] = collect($teams_2[2][0]);
            $teams_3[1][0]["win"] = 0;
        }
        if (!isset($teams_2[2][0]["status"]) && !isset($teams_2[2][0]["id"]) && isset($teams_2[2][1]["id"])) {
            $teams_2[2][1]["win"] = 1;
            $teams_3[1][0] = collect($teams_2[2][1]);
            $teams_3[1][0]["win"] = 0;
        }
        if (isset($teams_2[2][0]["id"]) && isset($teams_2[2][1]["id"])) {
            $teams_3[1][0]["status"] = "wait";
        }

        // round 2 match 4
        if (isset($teams_2[3][0]["id"]) && !isset($teams_2[3][1]["id"]) && !isset($teams_2[3][1]["status"])) {
            $teams_2[3][0]["win"] = 1;
            $teams_3[1][1] = collect($teams_2[3][0]);
            $teams_3[1][1]["win"] = 0;
        }
        if (!isset($teams_2[3][0]["status"]) && !isset($teams_2[3][0]["id"]) && isset($teams_2[3][1]["id"])) {
            $teams_2[3][1]["win"] = 1;
            $teams_3[1][1] = collect($teams_2[3][1]);
            $teams_3[1][1]["win"] = 0;
        }
        if (isset($teams_2[3][0]["id"]) && isset($teams_2[3][1]["id"])) {
            $teams_3[1][1]["status"] = "wait";
        }

        // round 2 match 5
        if (isset($teams_2[4][0]["id"]) && !isset($teams_2[4][1]["id"]) && !isset($teams_2[4][1]["status"])) {
            $teams_2[4][0]["win"] = 1;
            $teams_3[2][0] = collect($teams_2[4][0]);
            $teams_3[2][0]["win"] = 0;
        }
        if (!isset($teams_2[4][0]["status"]) && !isset($teams_2[4][0]["id"]) && isset($teams_2[4][1]["id"])) {
            $teams_2[4][1]["win"] = 1;
            $teams_3[2][0] = collect($teams_2[4][1]);
            $teams_3[2][0]["win"] = 0;
        }
        if (isset($teams_2[4][0]["id"]) && isset($teams_2[4][1]["id"])) {
            $teams_3[2][0]["status"] = "wait";
        }

        // round 2 match 6
        if (isset($teams_2[5][0]["id"]) && !isset($teams_2[5][1]["id"]) && !isset($teams_2[5][1]["status"])) {
            $teams_2[5][0]["win"] = 1;
            $teams_3[2][1] = collect($teams_2[5][0]);
            $teams_3[2][1]["win"] = 0;
        }
        if (!isset($teams_2[5][0]["status"]) && !isset($teams_2[5][0]["id"]) && isset($teams_2[5][1]["id"])) {
            $teams_2[5][1]["win"] = 1;
            $teams_3[2][1] = collect($teams_2[5][1]);
            $teams_3[2][1]["win"] = 0;
        }
        if (isset($teams_2[5][0]["id"]) && isset($teams_2[5][1]["id"])) {
            $teams_3[2][1]["status"] = "wait";
        }

        // round 2 match 7
        if (isset($teams_2[6][0]["id"]) && !isset($teams_2[6][1]["id"]) && !isset($teams_2[6][1]["status"])) {
            $teams_2[6][0]["win"] = 1;
            $teams_3[3][0] = collect($teams_2[6][0]);
            $teams_3[3][0]["win"] = 0;
        }
        if (!isset($teams_2[6][0]["status"]) && !isset($teams_2[6][0]["id"]) && isset($teams_2[6][1]["id"])) {
            $teams_2[6][1]["win"] = 1;
            $teams_3[3][0] = collect($teams_2[6][1]);
            $teams_3[3][0]["win"] = 0;
        }
        if (isset($teams_2[6][0]["id"]) && isset($teams_2[6][1]["id"])) {
            $teams_3[3][0]["status"] = "wait";
        }

        // round 2 match 8
        if (isset($teams_2[7][0]["id"]) && !isset($teams_2[7][1]["id"]) && !isset($teams_2[7][1]["status"])) {
            $teams_2[7][0]["win"] = 1;
            $teams_3[3][1] = collect($teams_2[7][0]);
            $teams_3[3][1]["win"] = 0;
        }
        if (!isset($teams_2[7][0]["status"]) && !isset($teams_2[7][0]["id"]) && isset($teams_2[7][1]["id"])) {
            $teams_2[7][1]["win"] = 1;
            $teams_3[3][1] = collect($teams_2[7][1]);
            $teams_3[3][1]["win"] = 0;
        }
        if (isset($teams_2[7][0]["id"]) && isset($teams_2[7][1]["id"])) {
            $teams_3[3][1]["status"] = "wait";
        }

        // round 3 match 1
        if (isset($teams_3[0][0]["id"]) && !isset($teams_3[0][1]["id"]) && !isset($teams_3[0][1]["status"])) {
            $teams_3[0][0]["win"] = 1;
            $teams_4[0][0] = collect($teams_3[0][0]);
            $teams_4[0][0]["win"] = 0;
        }
        if (!isset($teams_3[0][0]["status"]) && !isset($teams_3[0][0]["id"]) && isset($teams_3[0][1]["id"])) {
            $teams_3[0][1]["win"] = 1;
            $teams_4[0][0] = collect($teams_3[0][1]);
            $teams_4[0][0]["win"] = 0;
        }
        if (isset($teams_3[0][0]["id"]) && isset($teams_3[0][1]["id"])) {
            $teams_4[0][0]["status"] = "wait";
        }

        // round 3 match 2
        if (isset($teams_3[1][0]["id"]) && !isset($teams_3[1][1]["id"]) && !isset($teams_3[1][1]["status"])) {
            $teams_3[1][0]["win"] = 1;
            $teams_4[0][1] = collect($teams_3[1][0]);
            $teams_4[0][1]["win"] = 0;
        }
        if (!isset($teams_3[1][0]["status"]) && !isset($teams_3[1][0]["id"]) && isset($teams_3[1][1]["id"])) {
            $teams_3[1][1]["win"] = 1;
            $teams_4[0][1] = collect($teams_3[1][1]);
            $teams_4[0][1]["win"] = 0;
        }
        if (isset($teams_3[1][0]["id"]) && isset($teams_3[1][1]["id"])) {
            $teams_4[0][1]["status"] = "wait";
        }

        // round 3 match 3
        if (isset($teams_3[2][0]["id"]) && !isset($teams_3[2][1]["id"]) && !isset($teams_3[2][1]["status"])) {
            $teams_3[2][0]["win"] = 1;
            $teams_4[1][0] = collect($teams_3[2][0]);
            $teams_4[1][0]["win"] = 0;
        }
        if (!isset($teams_3[2][0]["status"]) && !isset($teams_3[2][0]["id"]) && isset($teams_3[2][1]["id"])) {
            $teams_3[2][1]["win"] = 1;
            $teams_4[1][0] = collect($teams_3[2][1]);
            $teams_4[1][0]["win"] = 0;
        }
        if (isset($teams_3[2][0]["id"]) && isset($teams_3[2][1]["id"])) {
            $teams_4[1][0]["status"] = "wait";
        }

        // round 3 match 4
        if (isset($teams_3[3][0]["id"]) && !isset($teams_3[3][1]["id"]) && !isset($teams_3[3][1]["status"])) {
            $teams_3[3][0]["win"] = 1;
            $teams_4[1][1] = collect($teams_3[3][0]);
            $teams_4[1][1]["win"] = 0;
        }
        if (!isset($teams_3[3][0]["status"]) && !isset($teams_3[3][0]["id"]) && isset($teams_3[3][1]["id"])) {
            $teams_3[3][1]["win"] = 1;
            $teams_4[1][1] = collect($teams_3[3][1]);
            $teams_4[1][1]["win"] = 0;
        }
        if (isset($teams_3[3][0]["id"]) && isset($teams_3[3][1]["id"])) {
            $teams_4[1][1]["status"] = "wait";
        }

        // round 4 match 1
        if (isset($teams_4[0][0]["id"]) && !isset($teams_4[0][1]["id"]) && !isset($teams_4[0][1]["status"])) {
            $teams_4[0][0]["win"] = 1;
            $teams_5[0][0] = collect($teams_4[0][0]);
            $teams_5[0][0]["win"] = 0;
        }
        if (!isset($teams_4[0][0]["status"]) && !isset($teams_4[0][0]["id"]) && isset($teams_4[0][1]["id"])) {
            $teams_4[0][1]["win"] = 1;
            $teams_5[0][0] = collect($teams_4[0][1]);
            $teams_5[0][0]["win"] = 0;
        }
        if (isset($teams_4[0][0]["id"]) && isset($teams_4[0][1]["id"])) {
            $teams_5[0][0]["status"] = "wait";
        }

        // round 4 match 2
        if (isset($teams_4[1][0]["id"]) && !isset($teams_4[1][1]["id"]) && !isset($teams_4[1][1]["status"])) {
            $teams_4[1][0]["win"] = 1;
            $teams_5[0][1] = collect($teams_4[1][0]);
            $teams_5[0][1]["win"] = 0;
        }
        if (!isset($teams_4[1][0]["status"]) && !isset($teams_4[1][0]["id"]) && isset($teams_4[1][1]["id"])) {
            $teams_4[1][1]["win"] = 1;
            $teams_5[0][1] = collect($teams_4[1][1]);
            $teams_5[0][1]["win"] = 0;
        }
        if (isset($teams_4[1][0]["id"]) && isset($teams_4[1][1]["id"])) {
            $teams_5[0][1]["status"] = "wait";
        }

        // ====================================================================================================

        $match_1 = ["round" => "round 1", "seeds" => [
            ["teams" => $teams[0]],
            ["teams" => $teams[1]],
            ["teams" => $teams[2]],
            ["teams" => $teams[3]],
            ["teams" => $teams[4]],
            ["teams" => $teams[5]],
            ["teams" => $teams[6]],
            ["teams" => $teams[7]],
            ["teams" => $teams[8]],
            ["teams" => $teams[9]],
            ["teams" => $teams[10]],
            ["teams" => $teams[11]],
            ["teams" => $teams[12]],
            ["teams" => $teams[13]],
            ["teams" => $teams[14]],
            ["teams" => $teams[15]]
        ]];

        $match_2 = ["round" => "round 2", "seeds" => [
            ["teams" => $teams_2[0]],
            ["teams" => $teams_2[1]],
            ["teams" => $teams_2[2]],
            ["teams" => $teams_2[3]],
            ["teams" => $teams_2[4]],
            ["teams" => $teams_2[5]],
            ["teams" => $teams_2[6]],
            ["teams" => $teams_2[7]]
        ]];
        $match_3 = ["round" => "round 3", "seeds" => [
            ["teams" => $teams_3[0]],
            ["teams" => $teams_3[1]],
            ["teams" => $teams_3[2]],
            ["teams" => $teams_3[3]],
        ]];

        $match_4 = ["round" => "round 4", "seeds" => [
            ["teams" => $teams_4[0]],
            ["teams" => $teams_4[1]],
        ]];

        $match_5 = ["round" => "gold", "seeds" => [
            ["teams" => $teams_5[0]],
        ]];
        $match_6 = ["round" => "bronze", "seeds" => [
            ["teams" => $teams_6[0]],
        ]];

        return [$match_1, $match_2, $match_3, $match_4, $match_5, $match_6];
    }

    public static function MakeTemplate16($members = [])
    {
        $elimination_member_count = 16;
        $members_coll = [];
        $members = array_slice($members, 0, $elimination_member_count);
        // error_log(\json_encode($members_coll[1]["member"]));
        for ($i = 0; $i < $elimination_member_count; $i++) {
            if (isset($members[$i]["member"])) {
                $arr = collect($members[$i]["member"]);
                $arr["postition"] = $i + 1;
                $arr["win"] = 0;
                $members_coll[$i] = $arr;
            } else
                $members_coll[$i] = [];
        }
        $teams = [];
        foreach (self::$match_potition[$elimination_member_count] as $key => $value) {
            $team = [];
            foreach ($value as $k => $v) {
                $i = $v - 1;
                $team[] = isset($members_coll[$i]) ? $members_coll[$i] : [];
            }
            $teams[] = $team;
        }
        $teams_2[0] = [[], []];
        $teams_2[1] = [[], []];
        $teams_2[2] = [[], []];
        $teams_2[3] = [[], []];

        $teams_3[0] = [[], []];
        $teams_3[1] = [[], []];

        $teams_4[0] = [[], []];

        $teams_5[0] = [[], []];
        // round 1 match 1
        if (isset($teams[0][0]["id"]) && !isset($teams[0][1]["id"])) {
            $teams_2[0][0] = collect($teams[0][0]);
            $teams[0][0]["win"] = 1;
            $teams_2[0][0]["win"] = 0;
        }
        if (isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])) {
            $teams_2[0][0]["status"] = "wait";
        }
        if (!isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])) {
            $teams[0][1]["win"] = 1;
            $teams_2[0][0] = collect($teams[0][1]);
            $teams_2[0][0]["win"] = 0;
        }

        // round 1 match 2
        if (isset($teams[1][0]["id"]) && !isset($teams[1][1]["id"])) {
            $teams[1][0]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][0]);
            $teams_2[0][1]["win"] = 0;
        }
        if (!isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])) {
            $teams[1][1]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][1]);
            $teams_2[0][1]["win"] = 0;
        }
        if (isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])) {
            $teams_2[0][1]["status"] = "wait";
        }

        // round 1 match 3
        if (isset($teams[2][0]["id"]) && !isset($teams[2][1]["id"])) {
            $teams[2][0]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][0]);
            $teams_2[1][0]["win"] = 0;
        }
        if (!isset($teams[2][0]["id"]) && isset($teams[2][1]["id"])) {
            $teams[2][1]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][1]);
            $teams_2[1][0]["win"] = 0;
        }
        if (isset($teams[2][0]["id"]) && isset($teams[2][1]["id"])) {
            $teams_2[1][0]["status"] = "wait";
        }

        // round 1 match 4
        if (isset($teams[3][0]["id"]) && !isset($teams[3][1]["id"])) {
            $teams[3][0]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][0]);
        }
        if (!isset($teams[3][0]["id"]) && isset($teams[3][1]["id"])) {
            $teams[3][1]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][1]);
            $teams_2[1][1]["win"] = 0;
        }
        if (isset($teams[3][0]["id"]) && isset($teams[3][1]["id"])) {
            $teams_2[1][1]["status"] = "wait";
        }

        // round 1 match 5
        if (isset($teams[4][0]["id"]) && !isset($teams[4][1]["id"])) {
            $teams[4][0]["win"] = 1;
            $teams_2[2][0] = collect($teams[4][0]);
            $teams_2[2][0]["win"] = 0;
        }
        if (!isset($teams[4][0]["id"]) && isset($teams[4][1]["id"])) {
            $teams[4][1]["win"] = 1;
            $teams_2[2][0] = collect($teams[4][1]);
            $teams_2[2][0]["win"] = 0;
        }
        if (isset($teams[4][0]["id"]) && isset($teams[4][1]["id"])) {
            $teams_2[2][0]["status"] = "wait";
        }

        // round 1 match 6
        if (isset($teams[5][0]["id"]) && !isset($teams[5][1]["id"])) {
            $teams[5][0]["win"] = 1;
            $teams_2[2][1] = collect($teams[5][0]);
            $teams_2[2][1]["win"] = 0;
        }
        if (!isset($teams[5][0]["id"]) && isset($teams[5][1]["id"])) {
            $teams[5][1]["win"] = 1;
            $teams_2[2][1] = collect($teams[5][1]);
            $teams_2[2][1]["win"] = 0;
        }
        if (isset($teams[5][0]["id"]) && isset($teams[5][1]["id"])) {
            $teams_2[2][1]["status"] = "wait";
        }

        // round 1 match 7
        if (isset($teams[6][0]["id"]) && !isset($teams[6][1]["id"])) {
            $teams[6][0]["win"] = 1;
            $teams_2[3][0] = collect($teams[6][0]);
            $teams_2[3][0]["win"] = 0;
        }
        if (!isset($teams[6][0]["id"]) && isset($teams[6][1]["id"])) {
            $teams[6][1]["win"] = 1;
            $teams_2[3][0] = collect($teams[6][1]);
            $teams_2[3][0]["win"] = 0;
        }
        if (isset($teams[6][0]["id"]) && isset($teams[6][1]["id"])) {
            $teams_2[3][0]["status"] = "wait";
        }

        // round 1 match 8
        if (isset($teams[7][0]["id"]) && !isset($teams[7][1]["id"])) {
            $teams[7][0]["win"] = 1;
            $teams_2[3][1] = collect($teams[7][0]);
            $teams_2[3][1]["win"] = 0;
        }
        if (!isset($teams[7][0]["id"]) && isset($teams[7][1]["id"])) {
            $teams[7][1]["win"] = 1;
            $teams_2[3][1] = collect($teams[7][1]);
            $teams_2[3][1]["win"] = 0;
        }
        if (isset($teams[7][0]["id"]) && isset($teams[7][1]["id"])) {
            $teams_2[3][1]["status"] = "wait";
        }

        // round 2 match 1
        if (isset($teams_2[0][0]["id"]) && !isset($teams_2[0][1]["id"]) && !isset($teams_2[0][1]["status"])) {
            $teams_2[0][0]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][0]);
            $teams_3[0][0]["win"] = 0;
        }
        if (!isset($teams_2[0][0]["status"]) && !isset($teams_2[0][0]["id"]) && isset($teams_2[0][1]["id"])) {
            $teams_2[0][1]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][1]);
            $teams_3[0][0]["win"] = 0;
        }
        if (isset($teams_2[0][0]["id"]) && isset($teams_2[0][1]["id"])) {
            $teams_3[0][0]["status"] = "wait";
        }

        // round 2 match 2
        if (isset($teams_2[1][0]["id"]) && !isset($teams_2[1][1]["id"]) && !isset($teams_2[1][1]["status"])) {
            $teams_2[1][0]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][0]);
            $teams_3[0][1]["win"] = 0;
        }
        if (!isset($teams_2[1][0]["status"]) && !isset($teams_2[1][0]["id"]) && isset($teams_2[1][1]["id"])) {
            $teams_2[1][1]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][1]);
            $teams_3[0][1]["win"] = 0;
        }
        if (isset($teams_2[1][0]["id"]) && isset($teams_2[1][1]["id"])) {
            $teams_3[0][1]["status"] = "wait";
        }

        // round 2 match 3
        if (isset($teams_2[2][0]["id"]) && !isset($teams_2[2][1]["id"]) && !isset($teams_2[2][1]["status"])) {
            $teams_2[2][0]["win"] = 1;
            $teams_3[1][0] = collect($teams_2[2][0]);
            $teams_3[1][0]["win"] = 0;
        }
        if (!isset($teams_2[2][0]["status"]) && !isset($teams_2[2][0]["id"]) && isset($teams_2[2][1]["id"])) {
            $teams_2[2][1]["win"] = 1;
            $teams_3[1][0] = collect($teams_2[2][1]);
            $teams_3[1][0]["win"] = 0;
        }
        if (isset($teams_2[2][0]["id"]) && isset($teams_2[2][1]["id"])) {
            $teams_3[1][0]["status"] = "wait";
        }

        // round 2 match 4
        if (isset($teams_2[3][0]["id"]) && !isset($teams_2[3][1]["id"]) && !isset($teams_2[3][1]["status"])) {
            $teams_2[3][0]["win"] = 1;
            $teams_3[1][1] = collect($teams_2[3][0]);
            $teams_3[1][1]["win"] = 0;
        }
        if (!isset($teams_2[3][0]["status"]) && !isset($teams_2[3][0]["id"]) && isset($teams_2[3][1]["id"])) {
            $teams_2[3][1]["win"] = 1;
            $teams_3[1][1] = collect($teams_2[3][1]);
            $teams_3[1][1]["win"] = 0;
        }
        if (isset($teams_2[3][0]["id"]) && isset($teams_2[3][1]["id"])) {
            $teams_3[1][1]["status"] = "wait";
        }

        // round 3 match 1
        if (isset($teams_3[0][0]["id"]) && !isset($teams_3[0][1]["id"]) && !isset($teams_3[0][1]["status"])) {
            $teams_3[0][0]["win"] = 1;
            $teams_4[0][0] = collect($teams_3[0][0]);
            $teams_4[0][0]["win"] = 0;
        }
        if (!isset($teams_3[0][0]["status"]) && !isset($teams_3[0][0]["id"]) && isset($teams_3[0][1]["id"])) {
            $teams_3[0][1]["win"] = 1;
            $teams_4[0][0] = collect($teams_3[0][1]);
            $teams_4[0][0]["win"] = 0;
        }
        if (isset($teams_3[0][0]["id"]) && isset($teams_3[0][1]["id"])) {
            $teams_4[0][0]["status"] = "wait";
        }

        // round 3 match 2
        if (isset($teams_3[1][0]["id"]) && !isset($teams_3[1][1]["id"]) && !isset($teams_3[1][1]["status"])) {
            $teams_3[1][0]["win"] = 1;
            $teams_4[0][1] = collect($teams_3[1][0]);
            $teams_4[0][1]["win"] = 0;
        }
        if (!isset($teams_3[1][0]["status"]) && !isset($teams_3[1][0]["id"]) && isset($teams_3[1][1]["id"])) {
            $teams_3[1][1]["win"] = 1;
            $teams_4[0][1] = collect($teams_3[1][1]);
            $teams_4[0][1]["win"] = 0;
        }
        if (isset($teams_3[1][0]["id"]) && isset($teams_3[1][1]["id"])) {
            $teams_4[0][1]["status"] = "wait";
        }

        $match_1 = ["round" => "round 1", "seeds" => [
            ["teams" => $teams[0]],
            ["teams" => $teams[1]],
            ["teams" => $teams[2]],
            ["teams" => $teams[3]],
            ["teams" => $teams[4]],
            ["teams" => $teams[5]],
            ["teams" => $teams[6]],
            ["teams" => $teams[7]]
        ]];

        $match_2 = ["round" => "round 2", "seeds" => [
            ["teams" => $teams_2[0]],
            ["teams" => $teams_2[1]],
            ["teams" => $teams_2[2]],
            ["teams" => $teams_2[3]]
        ]];
        $match_3 = ["round" => "round 3", "seeds" => [
            ["teams" => $teams_3[0]],
            ["teams" => $teams_3[1]],
        ]];
        $match_4 = ["round" => "gold", "seeds" => [
            ["teams" => $teams_4[0]],
        ]];
        $match_5 = ["round" => "bronze", "seeds" => [
            ["teams" => $teams_5[0]],
        ]];

        return [$match_1, $match_2, $match_3, $match_4, $match_5];
    }

    public static function MakeTemplate8($members = [])
    {
        $elimination_member_count = 8;
        $members_coll = [];
        $members = array_slice($members, 0, $elimination_member_count);
        // error_log(\json_encode($members_coll[1]["member"]));
        for ($i = 0; $i < $elimination_member_count; $i++) {
            if (isset($members[$i]["member"])) {
                $arr = collect($members[$i]["member"]);
                $arr["postition"] = $i + 1;
                $arr["win"] = 0;
                $members_coll[$i] = $arr;
            } else
                $members_coll[$i] = [];
        }
        $teams = [];
        foreach (self::$match_potition[$elimination_member_count] as $key => $value) {
            $team = [];
            foreach ($value as $k => $v) {
                $i = $v - 1;
                $team[] = isset($members_coll[$i]) ? $members_coll[$i] : [];
            }
            $teams[] = $team;
        }
        $teams_2[0] = [[], []];
        $teams_2[1] = [[], []];

        $teams_3[0] = [[], []];

        $teams_4[0] = [[], []];

        // round 1 match 1
        if (isset($teams[0][0]["id"]) && !isset($teams[0][1]["id"])) {
            $teams_2[0][0] = collect($teams[0][0]);
            $teams[0][0]["win"] = 1;
            $teams_2[0][0]["win"] = 0;
        }
        if (isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])) {
            $teams_2[0][0]["status"] = "wait";
        }
        if (!isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])) {
            $teams[0][1]["win"] = 1;
            $teams_2[0][0] = collect($teams[0][1]);
            $teams_2[0][0]["win"] = 0;
        }

        // round 1 match 2
        if (isset($teams[1][0]["id"]) && !isset($teams[1][1]["id"])) {
            $teams[1][0]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][0]);
            $teams_2[0][1]["win"] = 0;
        }
        if (!isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])) {
            $teams[1][1]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][1]);
            $teams_2[0][1]["win"] = 0;
        }
        if (isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])) {
            $teams_2[0][1]["status"] = "wait";
        }

        // round 1 match 3
        if (isset($teams[2][0]["id"]) && !isset($teams[2][1]["id"])) {
            $teams[2][0]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][0]);
            $teams_2[1][0]["win"] = 0;
        }
        if (!isset($teams[2][0]["id"]) && isset($teams[2][1]["id"])) {
            $teams[2][1]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][1]);
            $teams_2[1][0]["win"] = 0;
        }
        if (isset($teams[2][0]["id"]) && isset($teams[2][1]["id"])) {
            $teams_2[1][0]["status"] = "wait";
        }

        // round 1 match 4
        if (isset($teams[3][0]["id"]) && !isset($teams[3][1]["id"])) {
            $teams[3][0]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][0]);
        }
        if (!isset($teams[3][0]["id"]) && isset($teams[3][1]["id"])) {
            $teams[3][1]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][1]);
            $teams_2[1][1]["win"] = 0;
        }
        if (isset($teams[3][0]["id"]) && isset($teams[3][1]["id"])) {
            $teams_2[1][1]["status"] = "wait";
        }

        // round 2 match 1
        if (isset($teams_2[0][0]["id"]) && !isset($teams_2[0][1]["id"]) && !isset($teams_2[0][1]["status"])) {
            $teams_2[0][0]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][0]);
            $teams_3[0][0]["win"] = 0;
        }
        if (!isset($teams_2[0][0]["status"]) && !isset($teams_2[0][0]["id"]) && isset($teams_2[0][1]["id"])) {
            $teams_2[0][1]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][1]);
            $teams_3[0][0]["win"] = 0;
        }
        if (isset($teams_2[0][0]["id"]) && isset($teams_2[0][1]["id"])) {
            $teams_3[0][0]["status"] = "wait";
        }

        // round 2 match 2
        if (isset($teams_2[1][0]["id"]) && !isset($teams_2[1][1]["id"]) && !isset($teams_2[1][1]["status"])) {
            $teams_2[1][0]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][0]);
            $teams_3[0][1]["win"] = 0;
        }
        if (!isset($teams_2[1][0]["status"]) && !isset($teams_2[1][0]["id"]) && isset($teams_2[1][1]["id"])) {
            $teams_2[1][1]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][1]);
            $teams_3[0][1]["win"] = 0;
        }
        if (isset($teams_2[1][0]["id"]) && isset($teams_2[1][1]["id"])) {
            $teams_3[0][1]["status"] = "wait";
        }

        $match_1 = ["round" => "round 1", "seeds" => [
            ["teams" => $teams[0]],
            ["teams" => $teams[1]],
            ["teams" => $teams[2]],
            ["teams" => $teams[3]],
        ]];

        $match_2 = ["round" => "round 2", "seeds" => [
            ["teams" => $teams_2[0]],
            ["teams" => $teams_2[1]],
        ]];
        $match_3 = ["round" => "gold", "seeds" => [
            ["teams" => $teams_3[0]],
        ]];
        $match_4 = ["round" => "bronze", "seeds" => [
            ["teams" => $teams_4[0]],
        ]];

        return [$match_1, $match_2, $match_3, $match_4];
    }

    public static function MakeTemplate4($members = [])
    {
        $elimination_member_count = 4;
        $members_coll = [];
        $members = array_slice($members, 0, $elimination_member_count);
        // error_log(\json_encode($members_coll[1]["member"]));
        for ($i = 0; $i < $elimination_member_count; $i++) {
            if (isset($members[$i]["member"])) {
                $arr = collect($members[$i]["member"]);
                $arr["postition"] = $i + 1;
                $arr["win"] = 0;
                $members_coll[$i] = $arr;
            } else
                $members_coll[$i] = [];
        }
        $teams = [];
        foreach (self::$match_potition[$elimination_member_count] as $key => $value) {
            $team = [];
            foreach ($value as $k => $v) {
                $i = $v - 1;
                $team[] = isset($members_coll[$i]) ? $members_coll[$i] : [];
            }
            $teams[] = $team;
        }
        $teams_2[1] = [[], []];

        $teams_3[0] = [[], []];

        // round 1 match 1
        if (isset($teams[0][0]["id"]) && !isset($teams[0][1]["id"])) {
            $teams_2[0][0] = collect($teams[0][0]);
            $teams[0][0]["win"] = 1;
            $teams_2[0][0]["win"] = 0;
        }
        if (isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])) {
            $teams_2[0][0]["status"] = "wait";
        }
        if (!isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])) {
            $teams[0][1]["win"] = 1;
            $teams_2[0][0] = collect($teams[0][1]);
            $teams_2[0][0]["win"] = 0;
        }

        // round 1 match 2
        if (isset($teams[1][0]["id"]) && !isset($teams[1][1]["id"])) {
            $teams[1][0]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][0]);
            $teams_2[0][1]["win"] = 0;
        }
        if (!isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])) {
            $teams[1][1]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][1]);
            $teams_2[0][1]["win"] = 0;
        }
        if (isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])) {
            $teams_2[0][1]["status"] = "wait";
        }

        $match_1 = ["round" => "round 1", "seeds" => [
            ["teams" => $teams[0]],
            ["teams" => $teams[1]],
        ]];

        $match_2 = ["round" => "gold", "seeds" => [
            ["teams" => $teams_2[0]],
        ]];

        $match_3 = ["round" => "bronze", "seeds" => [
            ["teams" => $teams_3[0]],
        ]];

        return [$match_1, $match_2, $match_3];
    }

    public static function MakeTemplate4Team($team_club = [])
    {
        $elimination_team_count = 4;
        $team_coll = [];
        $team_club = array_slice($team_club, 0, $elimination_team_count);
        // error_log(\json_encode($members_coll[1]["member"]));
        // print_r(json_encode($team_club));
        // throw new BLoCException("ok");
        for ($i = 0; $i < $elimination_team_count; $i++) {
            if (isset($team_club[$i])) {
                $arr = collect($team_club[$i]);
                $arr["postition"] = $i + 1;
                $arr["win"] = 0;
                $team_coll[$i] = $arr;
            } else
                $team_coll[$i] = [];
        }
        $teams = [];
        foreach (self::$match_potition[$elimination_team_count] as $key => $value) {
            $team = [];
            foreach ($value as $k => $v) {
                $i = $v - 1;
                $team[] = isset($team_coll[$i]) ? $team_coll[$i] : [];
            }
            $teams[] = $team;
        }
        $teams_2[0] = [[], []];

        $teams_3[0] = [[], []];

        // round 1 match 1
        if (isset($teams[0][0]["participant_id"]) && !isset($teams[0][1]["participant_id"])) {
            $teams_2[0][0] = collect($teams[0][0]);
            $teams[0][0]["win"] = 1;
            $teams_2[0][0]["win"] = 0;
        }
        if (isset($teams[0][0]["participant_id"]) && isset($teams[0][1]["participant_id"])) {
            $teams_2[0][0]["status"] = "wait";
        }
        if (!isset($teams[0][0]["participant_id"]) && isset($teams[0][1]["participant_id"])) {
            $teams[0][1]["win"] = 1;
            $teams_2[0][0] = collect($teams[0][1]);
            $teams_2[0][0]["win"] = 0;
        }

        // round 1 match 2
        if (isset($teams[1][0]["participant_id"]) && !isset($teams[1][1]["participant_id"])) {
            $teams[1][0]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][0]);
            $teams_2[0][1]["win"] = 0;
        }
        if (!isset($teams[1][0]["participant_id"]) && isset($teams[1][1]["participant_id"])) {
            $teams[1][1]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][1]);
            $teams_2[0][1]["win"] = 0;
        }
        if (isset($teams[1][0]["participant_id"]) && isset($teams[1][1]["participant_id"])) {
            $teams_2[0][1]["status"] = "wait";
        }

        $match_1 = ["round" => "round 1", "seeds" => [
            ["teams" => $teams[0]],
            ["teams" => $teams[1]],
        ]];

        $match_2 = ["round" => "gold", "seeds" => [
            ["teams" => $teams_2[0]],
        ]];
        $match_3 = ["round" => "bronze", "seeds" => [
            ["teams" => $teams_3[0]],
        ]];

        return [$match_1, $match_2, $match_3];
    }

    public static function MakeTemplate8Team($team_club = [])
    {
        $elimination_team_count = 8;
        $team_coll = [];
        $team_club = array_slice($team_club, 0, $elimination_team_count);
        // error_log(\json_encode($members_coll[1]["member"]));
        // print_r(json_encode($team_club));
        // throw new BLoCException("ok");
        for ($i = 0; $i < $elimination_team_count; $i++) {
            if (isset($team_club[$i])) {
                $arr = collect($team_club[$i]);
                $arr["postition"] = $i + 1;
                $arr["win"] = 0;
                $team_coll[$i] = $arr;
            } else
                $team_coll[$i] = [];
        }
        $teams = [];
        foreach (self::$match_potition[$elimination_team_count] as $key => $value) {
            $team = [];
            foreach ($value as $k => $v) {
                $i = $v - 1;
                $team[] = isset($team_coll[$i]) ? $team_coll[$i] : [];
            }
            $teams[] = $team;
        }
        $teams_2[0] = [[], []];
        $teams_2[1] = [[], []];

        $teams_3[0] = [[], []];

        $teams_4[0] = [[], []];

        // round 1 match 1
        if (isset($teams[0][0]["participant_id"]) && !isset($teams[0][1]["participant_id"])) {
            $teams_2[0][0] = collect($teams[0][0]);
            $teams[0][0]["win"] = 1;
            $teams_2[0][0]["win"] = 0;
        }
        if (isset($teams[0][0]["participant_id"]) && isset($teams[0][1]["participant_id"])) {
            $teams_2[0][0]["status"] = "wait";
        }
        if (!isset($teams[0][0]["participant_id"]) && isset($teams[0][1]["participant_id"])) {
            $teams[0][1]["win"] = 1;
            $teams_2[0][0] = collect($teams[0][1]);
            $teams_2[0][0]["win"] = 0;
        }

        // round 1 match 2
        if (isset($teams[1][0]["participant_id"]) && !isset($teams[1][1]["participant_id"])) {
            $teams[1][0]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][0]);
            $teams_2[0][1]["win"] = 0;
        }
        if (!isset($teams[1][0]["participant_id"]) && isset($teams[1][1]["participant_id"])) {
            $teams[1][1]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][1]);
            $teams_2[0][1]["win"] = 0;
        }
        if (isset($teams[1][0]["participant_id"]) && isset($teams[1][1]["participant_id"])) {
            $teams_2[0][1]["status"] = "wait";
        }

        // round 1 match 3
        if (isset($teams[2][0]["participant_id"]) && !isset($teams[2][1]["participant_id"])) {
            $teams[2][0]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][0]);
            $teams_2[1][0]["win"] = 0;
        }
        if (!isset($teams[2][0]["participant_id"]) && isset($teams[2][1]["participant_id"])) {
            $teams[2][1]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][1]);
            $teams_2[1][0]["win"] = 0;
        }
        if (isset($teams[2][0]["participant_id"]) && isset($teams[2][1]["participant_id"])) {
            $teams_2[1][0]["status"] = "wait";
        }

        // round 1 match 4
        if (isset($teams[3][0]["participant_id"]) && !isset($teams[3][1]["participant_id"])) {
            $teams[3][0]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][0]);
        }
        if (!isset($teams[3][0]["participant_id"]) && isset($teams[3][1]["participant_id"])) {
            $teams[3][1]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][1]);
            $teams_2[1][1]["win"] = 0;
        }
        if (isset($teams[3][0]["participant_id"]) && isset($teams[3][1]["participant_id"])) {
            $teams_2[1][1]["status"] = "wait";
        }

        // round 2 match 1
        if (isset($teams_2[0][0]["participant_id"]) && !isset($teams_2[0][1]["participant_id"]) && !isset($teams_2[0][1]["status"])) {
            $teams_2[0][0]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][0]);
            $teams_3[0][0]["win"] = 0;
        }
        if (!isset($teams_2[0][0]["status"]) && !isset($teams_2[0][0]["participant_id"]) && isset($teams_2[0][1]["participant_id"])) {
            $teams_2[0][1]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][1]);
            $teams_3[0][0]["win"] = 0;
        }
        if (isset($teams_2[0][0]["participant_id"]) && isset($teams_2[0][1]["participant_id"])) {
            $teams_3[0][0]["status"] = "wait";
        }

        // round 2 match 2
        if (isset($teams_2[1][0]["participant_id"]) && !isset($teams_2[1][1]["participant_id"]) && !isset($teams_2[1][1]["status"])) {
            $teams_2[1][0]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][0]);
            $teams_3[0][1]["win"] = 0;
        }
        if (!isset($teams_2[1][0]["status"]) && !isset($teams_2[1][0]["participant_id"]) && isset($teams_2[1][1]["participant_id"])) {
            $teams_2[1][1]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][1]);
            $teams_3[0][1]["win"] = 0;
        }
        if (isset($teams_2[1][0]["participant_id"]) && isset($teams_2[1][1]["participant_id"])) {
            $teams_3[0][1]["status"] = "wait";
        }


        $match_1 = ["round" => "round 1", "seeds" => [
            ["teams" => $teams[0]],
            ["teams" => $teams[1]],
            ["teams" => $teams[2]],
            ["teams" => $teams[3]],
        ]];

        $match_2 = ["round" => "round 2", "seeds" => [
            ["teams" => $teams_2[0]],
            ["teams" => $teams_2[1]],
        ]];
        $match_3 = ["round" => "gold", "seeds" => [
            ["teams" => $teams_3[0]],
        ]];
        $match_4 = ["round" => "bronze", "seeds" => [
            ["teams" => $teams_4[0]],
        ]];

        return [$match_1, $match_2, $match_3, $match_4];
    }

    public static function MakeTemplate16Team($team_club = [])
    {
        $elimination_team_count = 16;
        $team_coll = [];
        $team_club = array_slice($team_club, 0, $elimination_team_count);
        for ($i = 0; $i < $elimination_team_count; $i++) {
            if (isset($team_club[$i])) {
                $arr = collect($team_club[$i]);
                $arr["postition"] = $i + 1;
                $arr["win"] = 0;
                $team_coll[$i] = $arr;
            } else
                $team_coll[$i] = [];
        }
        $teams = [];
        foreach (self::$match_potition[$elimination_team_count] as $key => $value) {
            $team = [];
            foreach ($value as $k => $v) {
                $i = $v - 1;
                $team[] = isset($team_coll[$i]) ? $team_coll[$i] : [];
            }
            $teams[] = $team;
        }
        $teams_2[0] = [[], []];
        $teams_2[1] = [[], []];
        $teams_2[2] = [[], []];
        $teams_2[3] = [[], []];

        $teams_3[0] = [[], []];
        $teams_3[1] = [[], []];

        $teams_4[0] = [[], []];

        $teams_5[0] = [[], []];
        // round 1 match 1
        if (isset($teams[0][0]["participant_id"]) && !isset($teams[0][1]["participant_id"])) {
            $teams_2[0][0] = collect($teams[0][0]);
            $teams[0][0]["win"] = 1;
            $teams_2[0][0]["win"] = 0;
        }
        if (isset($teams[0][0]["participant_id"]) && isset($teams[0][1]["participant_id"])) {
            $teams_2[0][0]["status"] = "wait";
        }
        if (!isset($teams[0][0]["participant_id"]) && isset($teams[0][1]["participant_id"])) {
            $teams[0][1]["win"] = 1;
            $teams_2[0][0] = collect($teams[0][1]);
            $teams_2[0][0]["win"] = 0;
        }

        // round 1 match 2
        if (isset($teams[1][0]["participant_id"]) && !isset($teams[1][1]["participant_id"])) {
            $teams[1][0]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][0]);
            $teams_2[0][1]["win"] = 0;
        }
        if (!isset($teams[1][0]["participant_id"]) && isset($teams[1][1]["participant_id"])) {
            $teams[1][1]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][1]);
            $teams_2[0][1]["win"] = 0;
        }
        if (isset($teams[1][0]["participant_id"]) && isset($teams[1][1]["participant_id"])) {
            $teams_2[0][1]["status"] = "wait";
        }

        // round 1 match 3
        if (isset($teams[2][0]["participant_id"]) && !isset($teams[2][1]["participant_id"])) {
            $teams[2][0]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][0]);
            $teams_2[1][0]["win"] = 0;
        }
        if (!isset($teams[2][0]["participant_id"]) && isset($teams[2][1]["participant_id"])) {
            $teams[2][1]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][1]);
            $teams_2[1][0]["win"] = 0;
        }
        if (isset($teams[2][0]["participant_id"]) && isset($teams[2][1]["participant_id"])) {
            $teams_2[1][0]["status"] = "wait";
        }

        // round 1 match 4
        if (isset($teams[3][0]["participant_id"]) && !isset($teams[3][1]["participant_id"])) {
            $teams[3][0]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][0]);
        }
        if (!isset($teams[3][0]["participant_id"]) && isset($teams[3][1]["participant_id"])) {
            $teams[3][1]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][1]);
            $teams_2[1][1]["win"] = 0;
        }
        if (isset($teams[3][0]["participant_id"]) && isset($teams[3][1]["participant_id"])) {
            $teams_2[1][1]["status"] = "wait";
        }

        // round 1 match 5
        if (isset($teams[4][0]["participant_id"]) && !isset($teams[4][1]["participant_id"])) {
            $teams[4][0]["win"] = 1;
            $teams_2[2][0] = collect($teams[4][0]);
            $teams_2[2][0]["win"] = 0;
        }
        if (!isset($teams[4][0]["participant_id"]) && isset($teams[4][1]["participant_id"])) {
            $teams[4][1]["win"] = 1;
            $teams_2[2][0] = collect($teams[4][1]);
            $teams_2[2][0]["win"] = 0;
        }
        if (isset($teams[4][0]["participant_id"]) && isset($teams[4][1]["participant_id"])) {
            $teams_2[2][0]["status"] = "wait";
        }

        // round 1 match 6
        if (isset($teams[5][0]["participant_id"]) && !isset($teams[5][1]["participant_id"])) {
            $teams[5][0]["win"] = 1;
            $teams_2[2][1] = collect($teams[5][0]);
            $teams_2[2][1]["win"] = 0;
        }
        if (!isset($teams[5][0]["participant_id"]) && isset($teams[5][1]["participant_id"])) {
            $teams[5][1]["win"] = 1;
            $teams_2[2][1] = collect($teams[5][1]);
            $teams_2[2][1]["win"] = 0;
        }
        if (isset($teams[5][0]["participant_id"]) && isset($teams[5][1]["participant_id"])) {
            $teams_2[2][1]["status"] = "wait";
        }

        // round 1 match 7
        if (isset($teams[6][0]["participant_id"]) && !isset($teams[6][1]["participant_id"])) {
            $teams[6][0]["win"] = 1;
            $teams_2[3][0] = collect($teams[6][0]);
            $teams_2[3][0]["win"] = 0;
        }
        if (!isset($teams[6][0]["participant_id"]) && isset($teams[6][1]["participant_id"])) {
            $teams[6][1]["win"] = 1;
            $teams_2[3][0] = collect($teams[6][1]);
            $teams_2[3][0]["win"] = 0;
        }
        if (isset($teams[6][0]["participant_id"]) && isset($teams[6][1]["participant_id"])) {
            $teams_2[3][0]["status"] = "wait";
        }

        // round 1 match 8
        if (isset($teams[7][0]["participant_id"]) && !isset($teams[7][1]["participant_id"])) {
            $teams[7][0]["win"] = 1;
            $teams_2[3][1] = collect($teams[7][0]);
            $teams_2[3][1]["win"] = 0;
        }
        if (!isset($teams[7][0]["participant_id"]) && isset($teams[7][1]["participant_id"])) {
            $teams[7][1]["win"] = 1;
            $teams_2[3][1] = collect($teams[7][1]);
            $teams_2[3][1]["win"] = 0;
        }
        if (isset($teams[7][0]["participant_id"]) && isset($teams[7][1]["participant_id"])) {
            $teams_2[3][1]["status"] = "wait";
        }

        // round 2 match 1
        if (isset($teams_2[0][0]["participant_id"]) && !isset($teams_2[0][1]["participant_id"]) && !isset($teams_2[0][1]["status"])) {
            $teams_2[0][0]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][0]);
            $teams_3[0][0]["win"] = 0;
        }
        if (!isset($teams_2[0][0]["status"]) && !isset($teams_2[0][0]["participant_id"]) && isset($teams_2[0][1]["participant_id"])) {
            $teams_2[0][1]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][1]);
            $teams_3[0][0]["win"] = 0;
        }
        if (isset($teams_2[0][0]["participant_id"]) && isset($teams_2[0][1]["participant_id"])) {
            $teams_3[0][0]["status"] = "wait";
        }

        // round 2 match 2
        if (isset($teams_2[1][0]["participant_id"]) && !isset($teams_2[1][1]["participant_id"]) && !isset($teams_2[1][1]["status"])) {
            $teams_2[1][0]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][0]);
            $teams_3[0][1]["win"] = 0;
        }
        if (!isset($teams_2[1][0]["status"]) && !isset($teams_2[1][0]["participant_id"]) && isset($teams_2[1][1]["participant_id"])) {
            $teams_2[1][1]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][1]);
            $teams_3[0][1]["win"] = 0;
        }
        if (isset($teams_2[1][0]["participant_id"]) && isset($teams_2[1][1]["participant_id"])) {
            $teams_3[0][1]["status"] = "wait";
        }

        // round 2 match 3
        if (isset($teams_2[2][0]["participant_id"]) && !isset($teams_2[2][1]["participant_id"]) && !isset($teams_2[2][1]["status"])) {
            $teams_2[2][0]["win"] = 1;
            $teams_3[1][0] = collect($teams_2[2][0]);
            $teams_3[1][0]["win"] = 0;
        }
        if (!isset($teams_2[2][0]["status"]) && !isset($teams_2[2][0]["participant_id"]) && isset($teams_2[2][1]["participant_id"])) {
            $teams_2[2][1]["win"] = 1;
            $teams_3[1][0] = collect($teams_2[2][1]);
            $teams_3[1][0]["win"] = 0;
        }
        if (isset($teams_2[2][0]["participant_id"]) && isset($teams_2[2][1]["participant_id"])) {
            $teams_3[1][0]["status"] = "wait";
        }

        // round 2 match 4
        if (isset($teams_2[3][0]["participant_id"]) && !isset($teams_2[3][1]["participant_id"]) && !isset($teams_2[3][1]["status"])) {
            $teams_2[3][0]["win"] = 1;
            $teams_3[1][1] = collect($teams_2[3][0]);
            $teams_3[1][1]["win"] = 0;
        }
        if (!isset($teams_2[3][0]["status"]) && !isset($teams_2[3][0]["participant_id"]) && isset($teams_2[3][1]["participant_id"])) {
            $teams_2[3][1]["win"] = 1;
            $teams_3[1][1] = collect($teams_2[3][1]);
            $teams_3[1][1]["win"] = 0;
        }
        if (isset($teams_2[3][0]["participant_id"]) && isset($teams_2[3][1]["participant_id"])) {
            $teams_3[1][1]["status"] = "wait";
        }

        // round 3 match 1
        if (isset($teams_3[0][0]["participant_id"]) && !isset($teams_3[0][1]["participant_id"]) && !isset($teams_3[0][1]["status"])) {
            $teams_3[0][0]["win"] = 1;
            $teams_4[0][0] = collect($teams_3[0][0]);
            $teams_4[0][0]["win"] = 0;
        }
        if (!isset($teams_3[0][0]["status"]) && !isset($teams_3[0][0]["participant_id"]) && isset($teams_3[0][1]["participant_id"])) {
            $teams_3[0][1]["win"] = 1;
            $teams_4[0][0] = collect($teams_3[0][1]);
            $teams_4[0][0]["win"] = 0;
        }
        if (isset($teams_3[0][0]["participant_id"]) && isset($teams_3[0][1]["participant_id"])) {
            $teams_4[0][0]["status"] = "wait";
        }

        // round 3 match 2
        if (isset($teams_3[1][0]["participant_id"]) && !isset($teams_3[1][1]["participant_id"]) && !isset($teams_3[1][1]["status"])) {
            $teams_3[1][0]["win"] = 1;
            $teams_4[0][1] = collect($teams_3[1][0]);
            $teams_4[0][1]["win"] = 0;
        }
        if (!isset($teams_3[1][0]["status"]) && !isset($teams_3[1][0]["participant_id"]) && isset($teams_3[1][1]["participant_id"])) {
            $teams_3[1][1]["win"] = 1;
            $teams_4[0][1] = collect($teams_3[1][1]);
            $teams_4[0][1]["win"] = 0;
        }
        if (isset($teams_3[1][0]["participant_id"]) && isset($teams_3[1][1]["participant_id"])) {
            $teams_4[0][1]["status"] = "wait";
        }

        $match_1 = ["round" => "round 1", "seeds" => [
            ["teams" => $teams[0]],
            ["teams" => $teams[1]],
            ["teams" => $teams[2]],
            ["teams" => $teams[3]],
            ["teams" => $teams[4]],
            ["teams" => $teams[5]],
            ["teams" => $teams[6]],
            ["teams" => $teams[7]]
        ]];

        $match_2 = ["round" => "round 2", "seeds" => [
            ["teams" => $teams_2[0]],
            ["teams" => $teams_2[1]],
            ["teams" => $teams_2[2]],
            ["teams" => $teams_2[3]]
        ]];
        $match_3 = ["round" => "round 3", "seeds" => [
            ["teams" => $teams_3[0]],
            ["teams" => $teams_3[1]],
        ]];
        $match_4 = ["round" => "gold", "seeds" => [
            ["teams" => $teams_4[0]],
        ]];
        $match_5 = ["round" => "bronze", "seeds" => [
            ["teams" => $teams_5[0]],
        ]];

        return [$match_1, $match_2, $match_3, $match_4, $match_5];
    }

    public static function MakeTemplate32Team($team_club = [])
    {
        $elimination_team_count = 32;
        $team_coll = [];
        $team_club = array_slice($team_club, 0, $elimination_team_count);

        for ($i = 0; $i < $elimination_team_count; $i++) {
            if (isset($team_club[$i])) {
                $arr = collect($team_club[$i]);
                $arr["postition"] = $i + 1;
                $arr["win"] = 0;
                $team_coll[$i] = $arr;
            } else
                $team_coll[$i] = [];
        }
        $teams = [];
        foreach (self::$match_potition[$elimination_team_count] as $key => $value) {
            $team = [];
            foreach ($value as $k => $v) {
                $i = $v - 1;
                $team[] = isset($team_coll[$i]) ? $team_coll[$i] : [];
            }
            $teams[] = $team;
        }

        $teams_2[0] = [[], []];
        $teams_2[1] = [[], []];
        $teams_2[2] = [[], []];
        $teams_2[3] = [[], []];
        $teams_2[4] = [[], []];
        $teams_2[5] = [[], []];
        $teams_2[6] = [[], []];
        $teams_2[7] = [[], []];

        $teams_3[0] = [[], []];
        $teams_3[1] = [[], []];
        $teams_3[2] = [[], []];
        $teams_3[3] = [[], []];

        $teams_4[0] = [[], []];
        $teams_4[1] = [[], []];

        $teams_5[0] = [[], []];

        $teams_6[0] = [[], []];

        // round 1 match 1
        if (isset($teams[0][0]["participant_id"]) && !isset($teams[0][1]["participant_id"])) {
            $teams_2[0][0] = collect($teams[0][0]);
            $teams[0][0]["win"] = 1;
            $teams_2[0][0]["win"] = 0;
        }
        if (isset($teams[0][0]["participant_id"]) && isset($teams[0][1]["participant_id"])) {
            $teams_2[0][0]["status"] = "wait";
        }
        if (!isset($teams[0][0]["participant_id"]) && isset($teams[0][1]["participant_id"])) {
            $teams[0][1]["win"] = 1;
            $teams_2[0][0] = collect($teams[0][1]);
            $teams_2[0][0]["win"] = 0;
        }

        // round 1 match 2
        if (isset($teams[1][0]["participant_id"]) && !isset($teams[1][1]["participant_id"])) {
            $teams[1][0]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][0]);
            $teams_2[0][1]["win"] = 0;
        }
        if (!isset($teams[1][0]["participant_id"]) && isset($teams[1][1]["participant_id"])) {
            $teams[1][1]["win"] = 1;
            $teams_2[0][1] = collect($teams[1][1]);
            $teams_2[0][1]["win"] = 0;
        }
        if (isset($teams[1][0]["participant_id"]) && isset($teams[1][1]["participant_id"])) {
            $teams_2[0][1]["status"] = "wait";
        }

        // round 1 match 3
        if (isset($teams[2][0]["participant_id"]) && !isset($teams[2][1]["participant_id"])) {
            $teams[2][0]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][0]);
            $teams_2[1][0]["win"] = 0;
        }
        if (!isset($teams[2][0]["participant_id"]) && isset($teams[2][1]["participant_id"])) {
            $teams[2][1]["win"] = 1;
            $teams_2[1][0] = collect($teams[2][1]);
            $teams_2[1][0]["win"] = 0;
        }
        if (isset($teams[2][0]["participant_id"]) && isset($teams[2][1]["participant_id"])) {
            $teams_2[1][0]["status"] = "wait";
        }

        // round 1 match 4
        if (isset($teams[3][0]["participant_id"]) && !isset($teams[3][1]["participant_id"])) {
            $teams[3][0]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][0]);
        }
        if (!isset($teams[3][0]["participant_id"]) && isset($teams[3][1]["participant_id"])) {
            $teams[3][1]["win"] = 1;
            $teams_2[1][1] = collect($teams[3][1]);
            $teams_2[1][1]["win"] = 0;
        }
        if (isset($teams[3][0]["participant_id"]) && isset($teams[3][1]["participant_id"])) {
            $teams_2[1][1]["status"] = "wait";
        }

        // round 1 match 5
        if (isset($teams[4][0]["participant_id"]) && !isset($teams[4][1]["participant_id"])) {
            $teams[4][0]["win"] = 1;
            $teams_2[2][0] = collect($teams[4][0]);
            $teams_2[2][0]["win"] = 0;
        }
        if (!isset($teams[4][0]["participant_id"]) && isset($teams[4][1]["participant_id"])) {
            $teams[4][1]["win"] = 1;
            $teams_2[2][0] = collect($teams[4][1]);
            $teams_2[2][0]["win"] = 0;
        }
        if (isset($teams[4][0]["participant_id"]) && isset($teams[4][1]["participant_id"])) {
            $teams_2[2][0]["status"] = "wait";
        }

        // round 1 match 6
        if (isset($teams[5][0]["participant_id"]) && !isset($teams[5][1]["participant_id"])) {
            $teams[5][0]["win"] = 1;
            $teams_2[2][1] = collect($teams[5][0]);
            $teams_2[2][1]["win"] = 0;
        }
        if (!isset($teams[5][0]["participant_id"]) && isset($teams[5][1]["participant_id"])) {
            $teams[5][1]["win"] = 1;
            $teams_2[2][1] = collect($teams[5][1]);
            $teams_2[2][1]["win"] = 0;
        }
        if (isset($teams[5][0]["participant_id"]) && isset($teams[5][1]["participant_id"])) {
            $teams_2[2][1]["status"] = "wait";
        }

        // round 1 match 7
        if (isset($teams[6][0]["participant_id"]) && !isset($teams[6][1]["participant_id"])) {
            $teams[6][0]["win"] = 1;
            $teams_2[3][0] = collect($teams[6][0]);
            $teams_2[3][0]["win"] = 0;
        }
        if (!isset($teams[6][0]["participant_id"]) && isset($teams[6][1]["participant_id"])) {
            $teams[6][1]["win"] = 1;
            $teams_2[3][0] = collect($teams[6][1]);
            $teams_2[3][0]["win"] = 0;
        }
        if (isset($teams[6][0]["participant_id"]) && isset($teams[6][1]["participant_id"])) {
            $teams_2[3][0]["status"] = "wait";
        }

        // round 1 match 8
        if (isset($teams[7][0]["participant_id"]) && !isset($teams[7][1]["participant_id"])) {
            $teams[7][0]["win"] = 1;
            $teams_2[3][1] = collect($teams[7][0]);
            $teams_2[3][1]["win"] = 0;
        }
        if (!isset($teams[7][0]["participant_id"]) && isset($teams[7][1]["participant_id"])) {
            $teams[7][1]["win"] = 1;
            $teams_2[3][1] = collect($teams[7][1]);
            $teams_2[3][1]["win"] = 0;
        }
        if (isset($teams[7][0]["participant_id"]) && isset($teams[7][1]["participant_id"])) {
            $teams_2[3][1]["status"] = "wait";
        }

        // round 1 match 9
        if (isset($teams[8][0]["participant_id"]) && !isset($teams[8][1]["participant_id"])) {
            $teams[8][0]["win"] = 1;
            $teams_2[4][0] = collect($teams[8][0]);
            $teams_2[4][0]["win"] = 0;
        }
        if (!isset($teams[8][0]["participant_id"]) && isset($teams[8][1]["participant_id"])) {
            $teams[8][1]["win"] = 1;
            $teams_2[4][0] = collect($teams[8][1]);
            $teams_2[4][0]["win"] = 0;
        }
        if (isset($teams[8][0]["participant_id"]) && isset($teams[8][1]["participant_id"])) {
            $teams_2[4][0]["status"] = "wait";
        }

        // round 1 match 10
        if (isset($teams[9][0]["participant_id"]) && !isset($teams[9][1]["participant_id"])) {
            $teams[9][0]["win"] = 1;
            $teams_2[4][1] = collect($teams[9][0]);
            $teams_2[4][1]["win"] = 0;
        }
        if (!isset($teams[9][0]["participant_id"]) && isset($teams[9][1]["participant_id"])) {
            $teams[9][1]["win"] = 1;
            $teams_2[4][1] = collect($teams[9][1]);
            $teams_2[4][1]["win"] = 0;
        }
        if (isset($teams[9][0]["participant_id"]) && isset($teams[9][1]["participant_id"])) {
            $teams_2[4][1]["status"] = "wait";
        }

        // round 1 match 11
        if (isset($teams[10][0]["participant_id"]) && !isset($teams[10][1]["participant_id"])) {
            $teams[10][0]["win"] = 1;
            $teams_2[5][0] = collect($teams[10][0]);
            $teams_2[5][0]["win"] = 0;
        }
        if (!isset($teams[10][0]["participant_id"]) && isset($teams[10][1]["participant_id"])) {
            $teams[10][1]["win"] = 1;
            $teams_2[5][0] = collect($teams[10][1]);
            $teams_2[5][0]["win"] = 0;
        }
        if (isset($teams[10][0]["participant_id"]) && isset($teams[10][1]["participant_id"])) {
            $teams_2[5][0]["status"] = "wait";
        }

        // round 1 match 12
        if (isset($teams[11][0]["participant_id"]) && !isset($teams[11][1]["participant_id"])) {
            $teams[11][0]["win"] = 1;
            $teams_2[5][1] = collect($teams[11][0]);
            $teams_2[5][1]["win"] = 0;
        }
        if (!isset($teams[11][0]["participant_id"]) && isset($teams[11][1]["participant_id"])) {
            $teams[11][1]["win"] = 1;
            $teams_2[5][1] = collect($teams[11][1]);
            $teams_2[5][1]["win"] = 0;
        }
        if (isset($teams[11][0]["participant_id"]) && isset($teams[11][1]["participant_id"])) {
            $teams_2[5][1]["status"] = "wait";
        }

        // round 1 match 13
        if (isset($teams[12][0]["participant_id"]) && !isset($teams[12][1]["participant_id"])) {
            $teams[12][0]["win"] = 1;
            $teams_2[6][0] = collect($teams[12][0]);
            $teams_2[6][0]["win"] = 0;
        }
        if (!isset($teams[12][0]["participant_id"]) && isset($teams[12][1]["participant_id"])) {
            $teams[12][1]["win"] = 1;
            $teams_2[6][0] = collect($teams[12][1]);
            $teams_2[6][0]["win"] = 0;
        }
        if (isset($teams[12][0]["participant_id"]) && isset($teams[12][1]["participant_id"])) {
            $teams_2[6][0]["status"] = "wait";
        }

        // round 1 match 14
        if (isset($teams[13][0]["participant_id"]) && !isset($teams[13][1]["participant_id"])) {
            $teams[13][0]["win"] = 1;
            $teams_2[6][1] = collect($teams[13][0]);
            $teams_2[6][1]["win"] = 0;
        }
        if (!isset($teams[13][0]["participant_id"]) && isset($teams[13][1]["participant_id"])) {
            $teams[13][1]["win"] = 1;
            $teams_2[6][1] = collect($teams[13][1]);
            $teams_2[6][1]["win"] = 0;
        }
        if (isset($teams[13][0]["participant_id"]) && isset($teams[13][1]["participant_id"])) {
            $teams_2[6][1]["status"] = "wait";
        }

        // round 1 match 15
        if (isset($teams[14][0]["participant_id"]) && !isset($teams[14][1]["participant_id"])) {
            $teams[14][0]["win"] = 1;
            $teams_2[7][0] = collect($teams[14][0]);
            $teams_2[7][0]["win"] = 0;
        }
        if (!isset($teams[14][0]["participant_id"]) && isset($teams[14][1]["participant_id"])) {
            $teams[14][1]["win"] = 1;
            $teams_2[7][0] = collect($teams[14][1]);
            $teams_2[7][0]["win"] = 0;
        }
        if (isset($teams[14][0]["participant_id"]) && isset($teams[14][1]["participant_id"])) {
            $teams_2[7][0]["status"] = "wait";
        }

        // round 1 match 16
        if (isset($teams[15][0]["participant_id"]) && !isset($teams[15][1]["participant_id"])) {
            $teams[15][0]["win"] = 1;
            $teams_2[7][1] = collect($teams[15][0]);
            $teams_2[7][1]["win"] = 0;
        }
        if (!isset($teams[15][0]["participant_id"]) && isset($teams[15][1]["participant_id"])) {
            $teams[15][1]["win"] = 1;
            $teams_2[7][1] = collect($teams[15][1]);
            $teams_2[7][1]["win"] = 0;
        }
        if (isset($teams[15][0]["participant_id"]) && isset($teams[15][1]["participant_id"])) {
            $teams_2[7][1]["status"] = "wait";
        }

        // round 2 match 1
        if (isset($teams_2[0][0]["participant_id"]) && !isset($teams_2[0][1]["participant_id"]) && !isset($teams_2[0][1]["status"])) {
            $teams_2[0][0]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][0]);
            $teams_3[0][0]["win"] = 0;
        }
        if (!isset($teams_2[0][0]["status"]) && !isset($teams_2[0][0]["participant_id"]) && isset($teams_2[0][1]["participant_id"])) {
            $teams_2[0][1]["win"] = 1;
            $teams_3[0][0] = collect($teams_2[0][1]);
            $teams_3[0][0]["win"] = 0;
        }
        if (isset($teams_2[0][0]["participant_id"]) && isset($teams_2[0][1]["participant_id"])) {
            $teams_3[0][0]["status"] = "wait";
        }

        // round 2 match 2
        if (isset($teams_2[1][0]["participant_id"]) && !isset($teams_2[1][1]["participant_id"]) && !isset($teams_2[1][1]["status"])) {
            $teams_2[1][0]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][0]);
            $teams_3[0][1]["win"] = 0;
        }
        if (!isset($teams_2[1][0]["status"]) && !isset($teams_2[1][0]["participant_id"]) && isset($teams_2[1][1]["participant_id"])) {
            $teams_2[1][1]["win"] = 1;
            $teams_3[0][1] = collect($teams_2[1][1]);
            $teams_3[0][1]["win"] = 0;
        }
        if (isset($teams_2[1][0]["participant_id"]) && isset($teams_2[1][1]["participant_id"])) {
            $teams_3[0][1]["status"] = "wait";
        }

        // round 2 match 3
        if (isset($teams_2[2][0]["participant_id"]) && !isset($teams_2[2][1]["participant_id"]) && !isset($teams_2[2][1]["status"])) {
            $teams_2[2][0]["win"] = 1;
            $teams_3[1][0] = collect($teams_2[2][0]);
            $teams_3[1][0]["win"] = 0;
        }
        if (!isset($teams_2[2][0]["status"]) && !isset($teams_2[2][0]["participant_id"]) && isset($teams_2[2][1]["participant_id"])) {
            $teams_2[2][1]["win"] = 1;
            $teams_3[1][0] = collect($teams_2[2][1]);
            $teams_3[1][0]["win"] = 0;
        }
        if (isset($teams_2[2][0]["participant_id"]) && isset($teams_2[2][1]["participant_id"])) {
            $teams_3[1][0]["status"] = "wait";
        }

        // round 2 match 4
        if (isset($teams_2[3][0]["participant_id"]) && !isset($teams_2[3][1]["participant_id"]) && !isset($teams_2[3][1]["status"])) {
            $teams_2[3][0]["win"] = 1;
            $teams_3[1][1] = collect($teams_2[3][0]);
            $teams_3[1][1]["win"] = 0;
        }
        if (!isset($teams_2[3][0]["status"]) && !isset($teams_2[3][0]["participant_id"]) && isset($teams_2[3][1]["participant_id"])) {
            $teams_2[3][1]["win"] = 1;
            $teams_3[1][1] = collect($teams_2[3][1]);
            $teams_3[1][1]["win"] = 0;
        }
        if (isset($teams_2[3][0]["participant_id"]) && isset($teams_2[3][1]["participant_id"])) {
            $teams_3[1][1]["status"] = "wait";
        }

        // round 2 match 5
        if (isset($teams_2[4][0]["participant_id"]) && !isset($teams_2[4][1]["participant_id"]) && !isset($teams_2[4][1]["status"])) {
            $teams_2[4][0]["win"] = 1;
            $teams_3[2][0] = collect($teams_2[4][0]);
            $teams_3[2][0]["win"] = 0;
        }
        if (!isset($teams_2[4][0]["status"]) && !isset($teams_2[4][0]["participant_id"]) && isset($teams_2[4][1]["participant_id"])) {
            $teams_2[4][1]["win"] = 1;
            $teams_3[2][0] = collect($teams_2[4][1]);
            $teams_3[2][0]["win"] = 0;
        }
        if (isset($teams_2[4][0]["participant_id"]) && isset($teams_2[4][1]["participant_id"])) {
            $teams_3[2][0]["status"] = "wait";
        }

        // round 2 match 6
        if (isset($teams_2[5][0]["participant_id"]) && !isset($teams_2[5][1]["participant_id"]) && !isset($teams_2[5][1]["status"])) {
            $teams_2[5][0]["win"] = 1;
            $teams_3[2][1] = collect($teams_2[5][0]);
            $teams_3[2][1]["win"] = 0;
        }
        if (!isset($teams_2[5][0]["status"]) && !isset($teams_2[5][0]["participant_id"]) && isset($teams_2[5][1]["participant_id"])) {
            $teams_2[5][1]["win"] = 1;
            $teams_3[2][1] = collect($teams_2[5][1]);
            $teams_3[2][1]["win"] = 0;
        }
        if (isset($teams_2[5][0]["participant_id"]) && isset($teams_2[5][1]["participant_id"])) {
            $teams_3[2][1]["status"] = "wait";
        }

        // round 2 match 7
        if (isset($teams_2[6][0]["participant_id"]) && !isset($teams_2[6][1]["participant_id"]) && !isset($teams_2[6][1]["status"])) {
            $teams_2[6][0]["win"] = 1;
            $teams_3[3][0] = collect($teams_2[6][0]);
            $teams_3[3][0]["win"] = 0;
        }
        if (!isset($teams_2[6][0]["status"]) && !isset($teams_2[6][0]["participant_id"]) && isset($teams_2[6][1]["participant_id"])) {
            $teams_2[6][1]["win"] = 1;
            $teams_3[3][0] = collect($teams_2[6][1]);
            $teams_3[3][0]["win"] = 0;
        }
        if (isset($teams_2[6][0]["participant_id"]) && isset($teams_2[6][1]["participant_id"])) {
            $teams_3[3][0]["status"] = "wait";
        }

        // round 2 match 8
        if (isset($teams_2[7][0]["participant_id"]) && !isset($teams_2[7][1]["participant_id"]) && !isset($teams_2[7][1]["status"])) {
            $teams_2[7][0]["win"] = 1;
            $teams_3[3][1] = collect($teams_2[7][0]);
            $teams_3[3][1]["win"] = 0;
        }
        if (!isset($teams_2[7][0]["status"]) && !isset($teams_2[7][0]["participant_id"]) && isset($teams_2[7][1]["participant_id"])) {
            $teams_2[7][1]["win"] = 1;
            $teams_3[3][1] = collect($teams_2[7][1]);
            $teams_3[3][1]["win"] = 0;
        }
        if (isset($teams_2[7][0]["participant_id"]) && isset($teams_2[7][1]["participant_id"])) {
            $teams_3[3][1]["status"] = "wait";
        }

        // round 3 match 1
        if (isset($teams_3[0][0]["participant_id"]) && !isset($teams_3[0][1]["participant_id"]) && !isset($teams_3[0][1]["status"])) {
            $teams_3[0][0]["win"] = 1;
            $teams_4[0][0] = collect($teams_3[0][0]);
            $teams_4[0][0]["win"] = 0;
        }
        if (!isset($teams_3[0][0]["status"]) && !isset($teams_3[0][0]["participant_id"]) && isset($teams_3[0][1]["participant_id"])) {
            $teams_3[0][1]["win"] = 1;
            $teams_4[0][0] = collect($teams_3[0][1]);
            $teams_4[0][0]["win"] = 0;
        }
        if (isset($teams_3[0][0]["participant_id"]) && isset($teams_3[0][1]["participant_id"])) {
            $teams_4[0][0]["status"] = "wait";
        }

        // round 3 match 2
        if (isset($teams_3[1][0]["participant_id"]) && !isset($teams_3[1][1]["participant_id"]) && !isset($teams_3[1][1]["status"])) {
            $teams_3[1][0]["win"] = 1;
            $teams_4[0][1] = collect($teams_3[1][0]);
            $teams_4[0][1]["win"] = 0;
        }
        if (!isset($teams_3[1][0]["status"]) && !isset($teams_3[1][0]["participant_id"]) && isset($teams_3[1][1]["participant_id"])) {
            $teams_3[1][1]["win"] = 1;
            $teams_4[0][1] = collect($teams_3[1][1]);
            $teams_4[0][1]["win"] = 0;
        }
        if (isset($teams_3[1][0]["participant_id"]) && isset($teams_3[1][1]["participant_id"])) {
            $teams_4[0][1]["status"] = "wait";
        }

        // round 3 match 3
        if (isset($teams_3[2][0]["participant_id"]) && !isset($teams_3[2][1]["participant_id"]) && !isset($teams_3[2][1]["status"])) {
            $teams_3[2][0]["win"] = 1;
            $teams_4[1][0] = collect($teams_3[2][0]);
            $teams_4[1][0]["win"] = 0;
        }
        if (!isset($teams_3[2][0]["status"]) && !isset($teams_3[2][0]["participant_id"]) && isset($teams_3[2][1]["participant_id"])) {
            $teams_3[2][1]["win"] = 1;
            $teams_4[1][0] = collect($teams_3[2][1]);
            $teams_4[1][0]["win"] = 0;
        }
        if (isset($teams_3[2][0]["participant_id"]) && isset($teams_3[2][1]["participant_id"])) {
            $teams_4[1][0]["status"] = "wait";
        }

        // round 3 match 4
        if (isset($teams_3[3][0]["participant_id"]) && !isset($teams_3[3][1]["participant_id"]) && !isset($teams_3[3][1]["status"])) {
            $teams_3[3][0]["win"] = 1;
            $teams_4[1][1] = collect($teams_3[3][0]);
            $teams_4[1][1]["win"] = 0;
        }
        if (!isset($teams_3[3][0]["status"]) && !isset($teams_3[3][0]["participant_id"]) && isset($teams_3[3][1]["participant_id"])) {
            $teams_3[3][1]["win"] = 1;
            $teams_4[1][1] = collect($teams_3[3][1]);
            $teams_4[1][1]["win"] = 0;
        }
        if (isset($teams_3[3][0]["participant_id"]) && isset($teams_3[3][1]["participant_id"])) {
            $teams_4[1][1]["status"] = "wait";
        }

        // round 4 match 1
        if (isset($teams_4[0][0]["participant_id"]) && !isset($teams_4[0][1]["participant_id"]) && !isset($teams_4[0][1]["status"])) {
            $teams_4[0][0]["win"] = 1;
            $teams_5[0][0] = collect($teams_4[0][0]);
            $teams_5[0][0]["win"] = 0;
        }
        if (!isset($teams_4[0][0]["status"]) && !isset($teams_4[0][0]["participant_id"]) && isset($teams_4[0][1]["participant_id"])) {
            $teams_4[0][1]["win"] = 1;
            $teams_5[0][0] = collect($teams_4[0][1]);
            $teams_5[0][0]["win"] = 0;
        }
        if (isset($teams_4[0][0]["participant_id"]) && isset($teams_4[0][1]["participant_id"])) {
            $teams_5[0][0]["status"] = "wait";
        }

        // round 4 match 2
        if (isset($teams_4[1][0]["participant_id"]) && !isset($teams_4[1][1]["participant_id"]) && !isset($teams_4[1][1]["status"])) {
            $teams_4[1][0]["win"] = 1;
            $teams_5[0][1] = collect($teams_4[1][0]);
            $teams_5[0][1]["win"] = 0;
        }
        if (!isset($teams_4[1][0]["status"]) && !isset($teams_4[1][0]["participant_id"]) && isset($teams_4[1][1]["participant_id"])) {
            $teams_4[1][1]["win"] = 1;
            $teams_5[0][1] = collect($teams_4[1][1]);
            $teams_5[0][1]["win"] = 0;
        }
        if (isset($teams_4[1][0]["participant_id"]) && isset($teams_4[1][1]["participant_id"])) {
            $teams_5[0][1]["status"] = "wait";
        }

        // ====================================================================================================

        $match_1 = ["round" => "round 1", "seeds" => [
            ["teams" => $teams[0]],
            ["teams" => $teams[1]],
            ["teams" => $teams[2]],
            ["teams" => $teams[3]],
            ["teams" => $teams[4]],
            ["teams" => $teams[5]],
            ["teams" => $teams[6]],
            ["teams" => $teams[7]],
            ["teams" => $teams[8]],
            ["teams" => $teams[9]],
            ["teams" => $teams[10]],
            ["teams" => $teams[11]],
            ["teams" => $teams[12]],
            ["teams" => $teams[13]],
            ["teams" => $teams[14]],
            ["teams" => $teams[15]]
        ]];

        $match_2 = ["round" => "round 2", "seeds" => [
            ["teams" => $teams_2[0]],
            ["teams" => $teams_2[1]],
            ["teams" => $teams_2[2]],
            ["teams" => $teams_2[3]],
            ["teams" => $teams_2[4]],
            ["teams" => $teams_2[5]],
            ["teams" => $teams_2[6]],
            ["teams" => $teams_2[7]]
        ]];
        $match_3 = ["round" => "round 3", "seeds" => [
            ["teams" => $teams_3[0]],
            ["teams" => $teams_3[1]],
            ["teams" => $teams_3[2]],
            ["teams" => $teams_3[3]],
        ]];

        $match_4 = ["round" => "round 4", "seeds" => [
            ["teams" => $teams_4[0]],
            ["teams" => $teams_4[1]],
        ]];

        $match_5 = ["round" => "gold", "seeds" => [
            ["teams" => $teams_5[0]],
        ]];
        $match_6 = ["round" => "bronze", "seeds" => [
            ["teams" => $teams_6[0]],
        ]];

        return [$match_1, $match_2, $match_3, $match_4, $match_5, $match_6];
    }
}
