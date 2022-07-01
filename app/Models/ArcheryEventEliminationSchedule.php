<?php

namespace App\Models;

use App\Libraries\EliminationFormat;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Database\Eloquent\Model;

class ArcheryEventEliminationSchedule extends Model
{
    protected $match_type = [
        "1" => "a_vs_z",
        "2" => "a_vs_b",
        "3" => "rand",
    ];

    protected $match_potition = [
        "16" => [
            [16, 1],
            [9, 8],
            [12, 5],
            [13, 4],
            [14, 3],
            [11, 6],
            [10, 7],
            [15, 2],
        ],
        "8" => [
            [8, 1],
            [5, 4],
            [6, 3],
            [7, 2]
        ],
    ];

    protected $match_elimination_position = [
        "16" => [
            1 => [0 => 7],
            2 => [0 => 6],
            3 => [0 => 5],
            4 => [1 => 1, 0 => 2],
            5 => [1 => 3, 0 => 4],
        ], //round=>win=>pos
        "8" => [
            1 => [0 => 6],
            2 => [0 => 5],
            3 => [1 => 1, 0 => 2],
            4 => [1 => 3, 0 => 4],
        ],
    ];


    protected function makeTemplate($members = [], $elimination_member_count = 16)
    {
        if ($elimination_member_count == 16) {
            return EliminationFormat::MakeTemplate16($members);
        }

        if ($elimination_member_count == 8) {
            return EliminationFormat::MakeTemplate8($members);
        }

        if ($elimination_member_count == 4) {
            return EliminationFormat::MakeTemplate4($members);
        }

        if ($elimination_member_count == 32) {
            return EliminationFormat::MakeTemplate32($members);
        }

        if ($elimination_member_count == 0) {
            throw new BLoCException("tentukan jumlah peserta eliminasi melihat bagan");
        }

        throw new BLoCException("template eliminasi tidak valid");
    }

    protected function makeTemplateTeam($team = [], $elimination_team_count = 16)
    {
        if ($elimination_team_count == 16) {
            return EliminationFormat::MakeTemplate16Team($team);
        }

        if ($elimination_team_count == 8) {
            return EliminationFormat::MakeTemplate8Team($team);
        }

        if ($elimination_team_count == 4) {
            return EliminationFormat::MakeTemplate4Team($team);
        }

        if ($elimination_team_count == 32) {
            return EliminationFormat::MakeTemplate32Team($team);
        }

        if ($elimination_team_count == 0) {
            throw new BLoCException("tentukan jumlah peserta eliminasi untuk melihat bagan");
        }

        throw new BLoCException("template eliminasi tidak valid");
    }

    protected function getTemplate($member_matches = [], $elimination_member_count = 16)
    {
        if ($elimination_member_count == 16) {
            return EliminationFormat::Template16($member_matches);
        }
        if ($elimination_member_count == 8) {
            return EliminationFormat::Template8($member_matches);
        }

        if ($elimination_member_count == 4) {
            return EliminationFormat::Template4($member_matches);
        }

        if ($elimination_member_count == 32) {
            return EliminationFormat::Template32($member_matches);
        }
    }

    protected function makeTemplate2($members = [], $elimination_member_count = 16, $match_type = 3, $event_category_id, $gender, $fix_members)
    {
        $team_per_seed =  2;
        // if(count($members) < $elimination_member_count)
        //     $elimination_member_count = count($members);
        $members = array_slice($members, 0, $elimination_member_count);
        for ($i = 0; $i < $elimination_member_count; $i++) {
            if (isset($members[$i]["member"]) && count($fix_members) < 1) {
                $members[$i]["member"]["postition"] = $i + 1;
                $members[$i] = $members[$i]["member"];
            } else
                $members[$i] = [];
        }

        $seeds = $this->makeSeeds($elimination_member_count, $team_per_seed);
        $templates = [];
        foreach ($seeds as $key => $value) {
            $seed = [];
            $teamMatch = [];
            if ($key == 0 && count($fix_members) < 1) {
                $teamMatch[$key] = $this->makeFirstMembersMatch($members, $value, $match_type, $team_per_seed);
            }
            for ($i = 0; $i < $value; $i++) {
                if (count($fix_members) < 1) {
                    $t = array(
                        "id" => $i,
                        "date" => "",
                        "teams" => isset($teamMatch[$key][$i]) ? $teamMatch[$key][$i] : [[], []],
                    );
                } else {
                    $t = array(
                        "id" => $i,
                        "date" => $fix_members[$key + 1][$i + 1]["date"],
                        "teams" => isset($fix_members[$key + 1][$i + 1]["members"]) ? $fix_members[$key + 1][$i + 1]["members"] : [[], []],
                    );
                }

                $seed[] = $t;
            }
            $templates[] = array("round" => $key + 1, "seeds" => $seed);
        }
        $clean_templates = $this->cleanMatch($templates);
        return $clean_templates;
    }

    protected function cleanMatch($templates = [])
    {
        $return_match = [];
        foreach ($templates as $key => $value) {
            foreach ($value["seeds"] as $k => $seed) {
                if (isset($return_match[$key])) {
                    $key_return_match = array_key_first($return_match[$key]);
                    if (empty($seed["teams"][0]) && isset($return_match[$key][$key_return_match]["status"]) && $return_match[$key][$key_return_match]["status"] == "win") {
                        if (empty($templates[$key]["seeds"][$k]["teams"][0]))
                            $templates[$key]["seeds"][$k]["teams"][0] = $return_match[$key][$key_return_match]["member"];
                    } else {
                        $templates[$key]["seeds"][$k]["status"][0] = $return_match[$key][$key_return_match]["status"];
                    }
                    unset($return_match[$key][$key_return_match]);

                    $key_return_match = array_key_first($return_match[$key]);
                    if (empty($seed["teams"][1]) && isset($return_match[$key][$key_return_match]["status"]) && $return_match[$key][$key_return_match]["status"] == "win") {
                        if (empty($templates[$key]["seeds"][$k]["teams"][1]))
                            $templates[$key]["seeds"][$k]["teams"][1] = $return_match[$key][$key_return_match]["member"];
                    } else {
                        $templates[$key]["seeds"][$k]["status"][1] = $return_match[$key][$key_return_match]["status"];
                    }
                    unset($return_match[$key][$key_return_match]);
                }

                $status = "empty";
                $member = [];
                if ((isset($templates[$key]["seeds"][$k]["status"][0]) && $templates[$key]["seeds"][$k]["status"][0] == "not_yet")
                    || (isset($templates[$key]["seeds"][$k]["status"][1]) && $templates[$key]["seeds"][$k]["status"][1] == "not_yet")
                ) {
                    $status = "not_yet";
                } else {
                    if (!empty($templates[$key]["seeds"][$k]["teams"][0]) && !empty($templates[$key]["seeds"][$k]["teams"][1])) {
                        $status = "not_yet";
                        $member = [];
                        if (isset($templates[$key]["seeds"][$k]["teams"][0]["win"]) && $templates[$key]["seeds"][$k]["teams"][0]["win"] == 1) {
                            $status = "win";
                            $member = $templates[$key]["seeds"][$k]["teams"][0];
                            $member["win"] = 0;
                        }
                        if (isset($templates[$key]["seeds"][$k]["teams"][1]["win"]) && $templates[$key]["seeds"][$k]["teams"][1]["win"] == 1) {
                            $status = "win";
                            $member = $templates[$key]["seeds"][$k]["teams"][1];
                            $member["win"] = 0;
                        }
                    } elseif (!empty($templates[$key]["seeds"][$k]["teams"][0]) || !empty($templates[$key]["seeds"][$k]["teams"][1])) {
                        $status = "not_yet";
                        $member = [];
                        if (!empty($templates[$key]["seeds"][$k]["teams"][0])) {
                            $status = "win";
                            $member = $templates[$key]["seeds"][$k]["teams"][0];
                            $member["win"] = 0;
                        }
                        if (!empty($templates[$key]["seeds"][$k]["teams"][1])) {
                            $status = "win";
                            $member = $templates[$key]["seeds"][$k]["teams"][1];
                            $member["win"] = 0;
                        }
                    }
                }

                $rm = array(
                    "status" => $status,
                    "member" => $member,
                );

                $return_match[$key + 1][$k] = $rm;
            }
        }

        return $templates;
    }

    protected function makeFirstMembersMatch($members, $member_seed_count, $match_type = "1", $team_per_seed = 2)
    {
        $teams = [];
        if ($match_type == "1") {
            foreach ($this->match_potition[$member_seed_count * $team_per_seed] as $key => $value) {
                $team = [];
                foreach ($value as $k => $v) {
                    $i = $v - 1;
                    $team[] = isset($members[$i]) ? $members[$i] : [];
                }
                $teams[] = $team;
            }

            // for ($i=0; $i < $member_seed_count; $i++) { 
            //     $team = [];
            //     for ($x=0; $x < $team_per_seed; $x++) { 
            //         if(count($members) > 0){
            //             $key = array_key_first($members);
            //             if(($x % $team_per_seed) > 0){
            //                 $key = array_key_last($members);
            //             }
            //             $team [] = $members[$key];
            //             unset($members[$key]);
            //         }else{
            //             $team [] = ["name" => ""];
            //         }
            //     }
            //     $teams[] = $team;
            // }
        }

        if ($match_type == "2") {
            for ($i = 0; $i < $member_seed_count; $i++) {
                $team = [];
                $m = $members;
                for ($x = 0; $x < $team_per_seed; $x++) {
                    if (isset($m[$x])) {
                        $team[] = $m[$x];
                        unset($members[$x]);
                    } else {
                        $team[] = ["name" => ""];
                    }
                }
                $members = array_values($members);
                $teams[] = $team;
            }
        }

        if ($match_type == "3") {
            for ($i = 0; $i < $member_seed_count; $i++) {
                $team = [];
                for ($x = 0; $x < $team_per_seed; $x++) {
                    if (count($members) > 0) {
                        $key = array_rand($members, 1);
                        $team[] = $members[$key];
                        unset($members[$key]);
                    } else {
                        $team[] = ["name" => ""];
                    }
                }
                $teams[] = $team;
            }
        }

        return $teams;
    }

    private function makeSeeds($elimination_member_count = 16, $team_per_seed)
    {
        $seeds = [];
        while ($elimination_member_count > 1) {
            $sisa_bagi = $elimination_member_count % $team_per_seed;
            $elimination_member_count = ($elimination_member_count - $sisa_bagi) / $team_per_seed;
            if ($sisa_bagi > 0) {
                $elimination_member_count = $elimination_member_count + 1;
            }
            $seeds[] = $elimination_member_count;
        }
        return $seeds;
    }
}
