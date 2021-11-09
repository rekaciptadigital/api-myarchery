<?php
namespace App\Libraries;
class EliminationFormat
{
    static $match_potition = [
        "16" => [
            [16,1],
            [9,8],
            [12,5],
            [13,4],
            [14,3],
            [11,6],
            [10,7],
            [15,2],
        ],
        "8" => [
            [8,1],
            [5,4],
            [6,3],
            [7,2]
        ],
    ];

    public static function Template16($members = []){
            $elimination_member_count = 16;
            $members_coll = collect($members)->all();
            $members_coll = array_slice($members_coll,0,$elimination_member_count);
            for ($i=0; $i < $elimination_member_count; $i++) { 
                if(isset($members[$i]["member"])){
                    $members_coll[$i]["member"]["postition"] = $i + 1;
                    $members_coll[$i]["member"]["win"] = 0;
                    $members_coll[$i] = $members_coll[$i]["member"];
                }else
                    $members_coll[$i] = [];
                \error_log($i);
            }
            $teams = [];
            foreach (self::$match_potition[$elimination_member_count] as $key => $value) {
                $team = [];
                foreach ($value as $k => $v) {
                    $i = $v-1;
                    $team [] = isset($members_coll[$i]) ? $members_coll[$i] : [];
                }
                $teams[] = $team;
            }
            $teams_2[0] = [[],[]];
            $teams_2[1] = [[],[]];
            $teams_2[2] = [[],[]];
            $teams_2[3] = [[],[]];

            $teams_3[0] = [[],[]];
            $teams_3[1] = [[],[]];
            
            $teams_4[0] = [[],[]];
            
            $teams_5[0] = [[],[]];
            // round 1 match 1
            if(isset($teams[0][0]["id"]) && !isset($teams[0][1]["id"])){
                $teams_2[0][0] = collect($teams[0][0]);
                $teams[0][0]["win"] = 1;
                $teams_2[0][0]["win"] = 0;
            }
            if(isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])){
                $teams_2[0][0]["status"] = "wait";
            }   
            if(!isset($teams[0][0]["id"]) && isset($teams[0][1]["id"])){
                $teams[0][1]["win"] = 1;
                $teams_2[0][0] = collect($teams[0][1]);
                $teams_2[0][0]["win"] = 0;
            }

            // round 1 match 2
            if(isset($teams[1][0]["id"]) && !isset($teams[1][1]["id"])){
                $teams[1][0]["win"] = 1;
                $teams_2[0][1] = collect($teams[1][0]);
                $teams_2[0][1]["win"] = 0;
            }   
            if(!isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])){
                $teams[1][1]["win"] = 1;
                $teams_2[0][1] = collect($teams[1][1]);
                $teams_2[0][1]["win"] = 0;
            }
            if(isset($teams[1][0]["id"]) && isset($teams[1][1]["id"])){
                $teams_2[0][1]["status"] = "wait";
            }

            // round 1 match 3
            if(isset($teams[2][0]["id"]) && !isset($teams[2][1]["id"])){
                $teams[2][0]["win"] = 1;
                $teams_2[1][0] = collect($teams[2][0]);
                $teams_2[1][0]["win"] = 0;
            }   
            if(!isset($teams[2][0]["id"]) && isset($teams[2][1]["id"])){
                $teams[2][1]["win"] = 1;
                $teams_2[1][0] = collect($teams[2][1]);
                $teams_2[1][0]["win"] = 0;
            }
            if(isset($teams[2][0]["id"]) && isset($teams[2][1]["id"])){
                $teams_2[1][0]["status"] = "wait";
            }

            // round 1 match 4
            if(isset($teams[3][0]["id"]) && !isset($teams[3][1]["id"])){
                $teams[3][0]["win"] = 1;
                $teams_2[1][1] = collect($teams[3][0]);
            }   
            if(!isset($teams[3][0]["id"]) && isset($teams[3][1]["id"])){
                $teams[3][1]["win"] = 1;
                $teams_2[1][1] = collect($teams[3][1]);
                $teams_2[1][1]["win"] = 0;
            }
            if(isset($teams[3][0]["id"]) && isset($teams[3][1]["id"])){
                $teams_2[1][1]["status"] = "wait";
            }

            // round 1 match 5
            if(isset($teams[4][0]["id"]) && !isset($teams[4][1]["id"])){
                $teams[4][0]["win"] = 1;
                $teams_2[2][0] = collect($teams[4][0]);
                $teams_2[2][0]["win"] = 0;
            }   
            if(!isset($teams[4][0]["id"]) && isset($teams[4][1]["id"])){
                $teams[4][1]["win"] = 1;
                $teams_2[2][0] = collect($teams[4][1]);
                $teams_2[2][0]["win"] = 0;
            }
            if(isset($teams[4][0]["id"]) && isset($teams[4][1]["id"])){
                $teams_2[2][0]["status"] = "wait";
            }

            // round 1 match 6
            if(isset($teams[5][0]["id"]) && !isset($teams[5][1]["id"])){
                $teams[5][0]["win"] = 1;
                $teams_2[2][1] = collect($teams[5][0]);
                $teams_2[2][1]["win"] = 0;
            }   
            if(!isset($teams[5][0]["id"]) && isset($teams[5][1]["id"])){
                $teams[5][1]["win"] = 1;
                $teams_2[2][1] = collect($teams[5][1]);
                $teams_2[2][1]["win"] = 0;
            }
            if(isset($teams[5][0]["id"]) && isset($teams[5][1]["id"])){
                $teams_2[2][1]["status"] = "wait";
            }

            // round 1 match 7
            if(isset($teams[6][0]["id"]) && !isset($teams[6][1]["id"])){
                $teams[6][0]["win"] = 1;
                $teams_2[3][0] = collect($teams[6][0]);
                $teams_2[3][0]["win"] = 0;
            }   
            if(!isset($teams[6][0]["id"]) && isset($teams[6][1]["id"])){
                $teams[6][1]["win"] = 1;
                $teams_2[3][0] = collect($teams[6][1]);
                $teams_2[3][0]["win"] = 0;
            }
            if(isset($teams[6][0]["id"]) && isset($teams[6][1]["id"])){
                $teams_2[3][0]["status"] = "wait";
            }

            // round 1 match 8
            if(isset($teams[7][0]["id"]) && !isset($teams[7][1]["id"])){
                $teams[7][0]["win"] = 1;
                $teams_2[3][1] = collect($teams[7][0]);
                $teams_2[3][1]["win"] = 0;
            }   
            if(!isset($teams[7][0]["id"]) && isset($teams[7][1]["id"])){
                $teams[7][1]["win"] = 1;
                $teams_2[3][1] = collect($teams[7][1]);
                $teams_2[3][1]["win"] = 0;
            }
            if(isset($teams[7][0]["id"]) && isset($teams[7][1]["id"])){
                $teams_2[3][1]["status"] = "wait";
            }

            // round 2 match 1
            if(isset($teams_2[0][0]["id"]) && !isset($teams_2[0][1]["id"]) && !isset($teams_2[0][1]["status"])){
                $teams_2[0][0]["win"] = 1;
                $teams_3[0][0] = collect($teams_2[0][0]);
                $teams_3[0][0]["win"] = 0;
            }
            if(!isset($teams_2[0][0]["status"]) && !isset($teams_2[0][0]["id"]) && isset($teams_2[0][1]["id"])){
                $teams_2[0][1]["win"] = 1;
                $teams_3[0][0] = collect($teams_2[0][1]);
                $teams_3[0][0]["win"] = 0;
            }   
            if(isset($teams_2[0][0]["id"]) && isset($teams_2[0][1]["id"])){
                $teams_3[0][0]["status"] = "wait";
            }

            // round 2 match 2
            if(isset($teams_2[1][0]["id"]) && !isset($teams_2[1][1]["id"]) && !isset($teams_2[1][1]["status"])){
                $teams_2[1][0]["win"] = 1;
                $teams_3[0][1] = collect($teams_2[1][0]);
                $teams_3[0][1]["win"] = 0;
            }
            if(!isset($teams_2[1][0]["status"]) && !isset($teams_2[1][0]["id"]) && isset($teams_2[1][1]["id"])){
                $teams_2[1][1]["win"] = 1;
                $teams_3[0][1] = collect($teams_2[1][1]);
                $teams_3[0][1]["win"] = 0;
            }   
            if(isset($teams_2[1][0]["id"]) && isset($teams_2[1][1]["id"])){
                $teams_3[0][1]["status"] = "wait";
            }

            // round 2 match 3
            if(isset($teams_2[2][0]["id"]) && !isset($teams_2[2][1]["id"]) && !isset($teams_2[2][1]["status"])){
                $teams_2[2][0]["win"] = 1;
                $teams_3[1][0] = collect($teams_2[2][0]);
                $teams_3[1][0]["win"] = 0;
            }
            if(!isset($teams_2[2][0]["status"]) && !isset($teams_2[2][0]["id"]) && isset($teams_2[2][1]["id"])){
                $teams_2[2][1]["win"] = 1;
                $teams_3[1][0] = collect($teams_2[2][1]);
                $teams_3[1][0]["win"] = 0;
            }   
            if(isset($teams_2[2][0]["id"]) && isset($teams_2[2][1]["id"])){
                $teams_3[1][0]["status"] = "wait";
            }

            // round 2 match 4
            if(isset($teams_2[3][0]["id"]) && !isset($teams_2[3][1]["id"]) && !isset($teams_2[3][1]["status"])){
                $teams_2[3][0]["win"] = 1;
                $teams_3[1][1] = collect($teams_2[3][0]);
                $teams_3[1][1]["win"] = 0;
            }
            if(!isset($teams_2[3][0]["status"]) && !isset($teams_2[3][0]["id"]) && isset($teams_2[3][1]["id"])){
                $teams_2[3][1]["win"] = 1;
                $teams_3[1][1] = collect($teams_2[3][1]);
                $teams_3[1][1]["win"] = 0;
            }   
            if(isset($teams_2[3][0]["id"]) && isset($teams_2[3][1]["id"])){
                $teams_3[1][1]["status"] = "wait";
            }

            // round 3 match 1
            if(isset($teams_3[0][0]["id"]) && !isset($teams_3[0][1]["id"]) && !isset($teams_3[0][1]["status"])){
                $teams_3[0][0]["win"] = 1;
                $teams_4[0][0] = collect($teams_3[0][0]);
                $teams_4[0][0]["win"] = 0;
            }
            if(!isset($teams_3[0][0]["status"]) && !isset($teams_3[0][0]["id"]) && isset($teams_3[0][1]["id"])){
                $teams_3[0][1]["win"] = 1;
                $teams_4[0][0] = collect($teams_3[0][1]);
                $teams_4[0][0]["win"] = 0;
            }   
            if(isset($teams_3[0][0]["id"]) && isset($teams_3[0][1]["id"])){
                $teams_4[0][0]["status"] = "wait";
            }

            // round 3 match 2
            if(isset($teams_3[1][0]["id"]) && !isset($teams_3[1][1]["id"]) && !isset($teams_3[1][1]["status"])){
                $teams_3[1][0]["win"] = 1;
                $teams_4[0][1] = collect($teams_3[1][0]);
                $teams_4[0][1]["win"] = 0;
            }
            if(!isset($teams_3[1][0]["status"]) && !isset($teams_3[1][0]["id"]) && isset($teams_3[1][1]["id"])){
                $teams_3[1][1]["win"] = 1;
                $teams_4[0][1] = collect($teams_3[1][1]);
                $teams_4[0][1]["win"] = 0;
            }   
            if(isset($teams_3[1][0]["id"]) && isset($teams_3[1][1]["id"])){
                $teams_4[0][1]["status"] = "wait";
            }

            $match_1 = ["round" => "round 1","seeds"=>[
                                                        ["teams" => $teams[0]],
                                                        ["teams" => $teams[1]],
                                                        ["teams" => $teams[2]],
                                                        ["teams" => $teams[3]],
                                                        ["teams" => $teams[4]],
                                                        ["teams" => $teams[5]],
                                                        ["teams" => $teams[6]],
                                                        ["teams" => $teams[7]]
                                                        ]];

            $match_2 = ["round" => "round 2","seeds"=>[
                                                        ["teams" => $teams_2[0]],
                                                        ["teams" => $teams_2[1]],
                                                        ["teams" => $teams_2[2]],
                                                        ["teams" => $teams_2[3]]
                                                        ]];
            $match_3 = ["round" => "round 3","seeds"=>[
                                                        ["teams" => $teams_3[0]],
                                                        ["teams" => $teams_3[1]],
                                                        ]];
            $match_4 = ["round" => "gold","seeds"=>[
                                                        ["teams" => $teams_4[0]],
                                                        ]];
            $match_5 = ["round" => "bronze","seeds"=>[
                                                        ["teams" => $teams_4[0]],
                                                        ]];
            return [$match_1,$match_2,$match_3,$match_4,$match_5];
    }
}