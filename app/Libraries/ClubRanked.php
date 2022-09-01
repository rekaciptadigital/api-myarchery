<?php

namespace App\Libraries;

use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Models\ArcheryScoring;
use App\Models\City;
use DAI\Utils\Exceptions\BLoCException;

class ClubRanked
{

    public static function getEventRanked($event_id, $rules_rating_club = null, $group_category_id = null, $age_category_id = null, $competition_category_id = null, $distance_id = null)
    {
        // dd($rules_rating_club."-".$group_category_id);
        $output = [];
        $club_ids = [];
        $cat_detail = [];
        $max_pos = 4;
        $members = ArcheryEventParticipantMember::select(
            "archery_event_elimination_members.*",
            "archery_event_participants.club_id",
            "archery_event_participants.event_category_id"
        )->join(
            "archery_event_participants",
            "archery_event_participant_members.archery_event_participant_id",
            "=",
            "archery_event_participants.id"
        )->join(
            "archery_event_category_details",
            "archery_event_category_details.id",
            "=",
            "archery_event_participants.event_category_id"
        )->join(
            "archery_event_elimination_members",
            "archery_event_participant_members.id",
            "=",
            "archery_event_elimination_members.member_id"
        )->where(function ($query) use ($max_pos) {
            return $query->where('archery_event_elimination_members.position_qualification', '<', $max_pos)
                ->orWhere('archery_event_elimination_members.elimination_ranked', '<', $max_pos);
        })->where("archery_event_participants.event_id", $event_id)
            ->when($age_category_id, function ($query, $age_category_id) {
                $query->where('archery_event_category_details.age_category_id', $age_category_id);
            })
            ->when($competition_category_id, function ($query, $competition_category_id) {
                $query->where('archery_event_category_details.competition_category_id', $competition_category_id);
            })
            ->when($distance_id, function ($query, $distance_id) {
                $query->where('archery_event_category_details.distance_id', $distance_id);
            })
            ->where(function ($query) use ($rules_rating_club, $group_category_id) {
                // if ($rules_rating_club == 1 && $group_category_id != 0) {
                //     return $query->where("archery_event_category_details.group_category_id", $group_category_id)
                //         ->where("archery_event_category_details.rules_rating_club", $rules_rating_club);
                // } elseif ($rules_rating_club == 1 && $group_category_id == 0) {
                //     return $query->where("archery_event_category_details.rules_rating_club", $rules_rating_club);
                // }

                if ($rules_rating_club == 1) {
                    if ($group_category_id == 0) {
                        return $query->where("archery_event_category_details.rules_rating_club", $rules_rating_club);
                    } else {
                        return $query->where("archery_event_category_details.group_category_id", $group_category_id)
                            ->where("archery_event_category_details.rules_rating_club", $rules_rating_club);
                    }
                } else {
                    return $query->where("archery_event_category_details.group_category_id", $group_category_id)
                        ->where("archery_event_category_details.rules_rating_club", $rules_rating_club);
                }
            })
            ->where("archery_event_participants.club_id", "!=", 0)
            ->where("archery_event_participants.status", 1)
            ->get();

        foreach ($members as $key => $value) {
            if (!isset($cat_detail[$value->event_category_id])) {
                $category_detail = ArcheryEventCategoryDetail::where("id", $value->event_category_id)->first();
                $cat_detail[$value->event_category_id] = $category_detail;
            } else {
                $category_detail = $cat_detail[$value->event_category_id];
            }

            $medal_qualification = self::getMedalByPos($value->position_qualification);
            if (!empty($medal_qualification)) {
                $club_ids[$value->club_id][$medal_qualification] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id][$medal_qualification]) ? $club_ids[$value->club_id][$medal_qualification] + 1 : 1;
                $club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_qualification] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_qualification]) ? $club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_qualification] + 1 : 1;
            }

            $medal_elimination = self::getMedalByPos($value->elimination_ranked);
            if (!empty($medal_elimination)) {
                $club_ids[$value->club_id][$medal_elimination] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id][$medal_elimination]) ? $club_ids[$value->club_id][$medal_elimination] + 1 : 1;
                $club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_elimination] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_elimination]) ? $club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_elimination] + 1 : 1;
            }
        }

        // TODO SEMENTARA
        $teams = ArcheryEventCategoryDetail::where("event_id", $event_id)
            ->whereIn("team_category_id", ["male_team", "female_team", "mix_team"])
            ->when($age_category_id, function ($query, $age_category_id) {
                $query->where('age_category_id', $age_category_id);
            })
            ->when($competition_category_id, function ($query, $competition_category_id) {
                $query->where('archery_event_category_details.competition_category_id', $competition_category_id);
            })
            ->when($distance_id, function ($query, $distance_id) {
                $query->where('archery_event_category_details.distance_id', $distance_id);
            })

            ->where(function ($query) use ($rules_rating_club, $group_category_id) {
                if ($rules_rating_club == 1) {
                    if ($group_category_id == 0) {
                        return $query->where("rules_rating_club", $rules_rating_club);
                    } else {
                        return $query->where("group_category_id", $group_category_id)
                            ->where("rules_rating_club", $rules_rating_club);
                    }
                } else {
                    return $query->where("group_category_id", $group_category_id)
                        ->where("rules_rating_club", $rules_rating_club);
                }
            })
            ->get();

        foreach ($teams as $t => $team) {
            if (!isset($cat_detail[$team->id]))
                $cat_detail[$team->id] = $team;

            $elimination_group = ArcheryEventEliminationGroup::where("category_id", $team->id)->first();
            if ($elimination_group) {
                continue;
            }

            $session = [];
            for ($i = 0; $i < $team->session_in_qualification; $i++) {
                $session[] = $i + 1;
            }

            $category_detail_male = ArcheryEventCategoryDetail::where("event_id", $team->event_id)
                ->where("age_category_id", $team->age_category_id)
                ->where("competition_category_id", $team->competition_category_id)
                ->where("distance_id", $team->distance_id)
                ->where("team_category_id", "individu male")->first();

            $category_detail_femaie = ArcheryEventCategoryDetail::where("event_id", $team->event_id)
                ->where("age_category_id", $team->age_category_id)
                ->where("competition_category_id", $team->competition_category_id)
                ->where("distance_id", $team->distance_id)
                ->where("team_category_id", "individu female")->first();

            // dapatin rank kualifikasi beregu
            if ($team->team_category_id == "mix_team") {
                if ($category_detail_male && $category_detail_femaie) {
                    $elimination_individu_male = ArcheryEventElimination::where("event_category_id", $category_detail_male->id)->first();
                    $elimination_individu_female = ArcheryEventElimination::where("event_category_id", $category_detail_femaie->id)->first();
                    if (!$elimination_individu_male || !$elimination_individu_female) {
                        continue;
                    }
                } else {
                    continue;
                }

                $mix_ranked = self::getRankedMixTeam($team, $session);
                $mix_pos = 0;
                foreach ($mix_ranked as $mr => $mrank) {
                    $mix_pos = $mix_pos + 1;
                    if ($mrank["total"] < 1) continue;

                    $medal_mix_team = self::getMedalByPos($mix_pos);
                    if (!empty($medal_mix_team)) {
                        $club_ids[$mrank["club_id"]][$medal_mix_team] = isset($club_ids[$mrank["club_id"]]) && isset($club_ids[$mrank["club_id"]][$medal_mix_team]) ? $club_ids[$mrank["club_id"]][$medal_mix_team] + 1 : 1;
                        $club_ids[$mrank["club_id"]]["detail_medal"]["category"][$team->competition_category_id][$team->age_category_id][$medal_mix_team] = isset($club_ids[$mrank["club_id"]]) && isset($club_ids[$mrank["club_id"]]["detail_medal"]["category"][$team->competition_category_id][$team->age_category_id][$medal_mix_team]) ? $club_ids[$mrank["club_id"]]["detail_medal"]["category"][$team->competition_category_id][$team->age_category_id][$medal_mix_team] + 1 : 1;
                    }

                    if ($mix_pos >= 3) break;
                }
            } else {
                if ($team->team_category_id == "male_team") {
                    if ($category_detail_male) {
                        $elimination_individu_male = ArcheryEventElimination::where("event_category_id", $category_detail_male->id)->first();
                        if (!$elimination_individu_male) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

                if ($team->team_category_id == "female_team") {
                    if ($category_detail_femaie) {
                        $elimination_individu_female = ArcheryEventElimination::where("event_category_id", $category_detail_femaie->id)->first();
                        if (!$elimination_individu_female) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

                $ranked = self::getRankedTeam($team, $session);
                $pos = 0;
                foreach ($ranked as $r => $rank) {
                    $pos = $pos + 1;
                    if ($rank["total"] < 1) continue;

                    $medal_team = self::getMedalByPos($pos);
                    if (!empty($medal_team)) {
                        $club_ids[$rank["club_id"]][$medal_team] = isset($club_ids[$rank["club_id"]]) && isset($club_ids[$rank["club_id"]][$medal_team]) ? $club_ids[$rank["club_id"]][$medal_team] + 1 : 1;
                        $club_ids[$rank["club_id"]]["detail_medal"]["category"][$team->competition_category_id][$team->age_category_id][$medal_team] = isset($club_ids[$rank["club_id"]]) && isset($club_ids[$rank["club_id"]]["detail_medal"]["category"][$team->competition_category_id][$team->age_category_id][$medal_team]) ? $club_ids[$rank["club_id"]]["detail_medal"]["category"][$team->competition_category_id][$team->age_category_id][$medal_team] + 1 : 1;
                    }

                    if ($pos >= 3) break;
                }
                // print_r($ranked);
            }
        }

        // dapatkan data eliminasi beregu

        $group = ArcheryEventParticipant::select(
            "archery_event_elimination_group_teams.*",
            "archery_event_participants.club_id",
            "archery_event_participants.event_category_id"
        )
            ->join("archery_event_elimination_group_teams", "archery_event_participants.id", "=", "archery_event_elimination_group_teams.participant_id")
            ->join(
                "archery_event_category_details",
                "archery_event_category_details.id",
                "=",
                "archery_event_participants.event_category_id"
            )->where(function ($query) use ($max_pos) {
                return $query->where('archery_event_elimination_group_teams.position', '<', $max_pos)
                    ->orWhere('archery_event_elimination_group_teams.elimination_ranked', '<', $max_pos);
            })->where("archery_event_participants.event_id", $event_id)
            ->when($age_category_id, function ($query, $age_category_id) {
                $query->where('archery_event_category_details.age_category_id', $age_category_id);
            })
            ->when($competition_category_id, function ($query, $competition_category_id) {
                $query->where('archery_event_category_details.competition_category_id', $competition_category_id);
            })
            ->when($distance_id, function ($query, $distance_id) {
                $query->where('archery_event_category_details.distance_id', $distance_id);
            })
            ->where(function ($query) use ($rules_rating_club, $group_category_id) {
                if ($rules_rating_club == 1) {
                    if ($group_category_id == 0) {
                        return $query->where("archery_event_category_details.rules_rating_club", $rules_rating_club);
                    } else {
                        return $query->where("archery_event_category_details.group_category_id", $group_category_id)
                            ->where("archery_event_category_details.rules_rating_club", $rules_rating_club);
                    }
                } else {
                    return $query->where("archery_event_category_details.group_category_id", $group_category_id)
                        ->where("archery_event_category_details.rules_rating_club", $rules_rating_club);
                }
            })
            ->where("archery_event_participants.club_id", "!=", 0)
            ->where("archery_event_participants.status", 1)
            ->get();

        // return $group;

        foreach ($group as $key => $value) {
            if (!isset($cat_detail[$value->event_category_id])) {
                $category_detail = ArcheryEventCategoryDetail::where("id", $value->event_category_id)->first();
                $cat_detail[$value->event_category_id] = $category_detail;
            } else {
                $category_detail = $cat_detail[$value->event_category_id];
            }

            $medal_elimination = self::getMedalByPos($value->elimination_ranked);
            if (!empty($medal_elimination)) {
                $club_ids[$value->club_id][$medal_elimination] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id][$medal_elimination]) ? $club_ids[$value->club_id][$medal_elimination] + 1 : 1;
                $club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_elimination] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_elimination]) ? $club_ids[$value->club_id]["detail_medal"]["category"][$category_detail->competition_category_id][$category_detail->age_category_id][$medal_elimination] + 1 : 1;
            }
        }

        foreach ($club_ids as $k => $v) {
            $club = ArcheryClub::find($k);

            if (!$club) continue;

            $city = City::find($club->city);

            $bronze = isset($v["bronze"]) ? $v["bronze"] : 0;
            $gold = isset($v["gold"]) ? $v["gold"] : 0;
            $silver = isset($v["silver"]) ? $v["silver"] : 0;
            $total_gold = $gold;
            $total_silver = $silver;
            $total_bronze = $bronze;
            $elimination_team_medal = null;
            $output[] = [
                "club_name" => $club->name,
                "club_logo" => $club->logo,
                "club_city" => $city ? $city->name : "",
                "detail_medal" => $v["detail_medal"],
                "elimination_team_medal" => $elimination_team_medal,
                "gold" => $total_gold,
                "silver" => $total_silver,
                "bronze" => $total_bronze,
                "total" => $total_gold + $total_silver + $total_bronze
            ];
        }

        usort($output, function ($a, $b) {
            if ($a["gold"] == $b["gold"]) {
                if ($a["silver"] == $b["silver"]) {
                    if ($a["bronze"] == $b["bronze"]) {
                        return -1;
                    }
                    if ($a["bronze"] < $b["bronze"]) {
                        return 1;
                    }
                }
                if ($a["silver"] < $b["silver"]) {
                    return 1;
                }
            }
            if ($a["gold"] < $b["gold"]) {
                return 1;
            }
            return -1;
        });

        return $output;
    }

    public static function getRankedTeam($category_detail, $session)
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
        $team_cat = ($category_detail->team_category_id) == "male_team" ? "individu male" : "individu female";
        $category_detail_team = ArcheryEventCategoryDetail::where("event_id", $category_detail->event_id)
            ->where("age_category_id", $category_detail->age_category_id)
            ->where("competition_category_id", $category_detail->competition_category_id)
            ->where("distance_id", $category_detail->distance_id)
            ->where("team_category_id", $team_cat)->first();
        $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category_detail_team->id, 1, $session);

        $participant_club = [];
        $sequence_club = [];
        $participants = ArcheryEventParticipant::select("archery_event_participants.*", "archery_clubs.name as club_name")
            ->where("event_category_id", $category_detail->id)
            ->where("status", 1)
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")->get();
        foreach ($participants as $key => $value) {
            $club_members = [];
            $total_per_point = $total_per_points;
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
                "team" => $value->club_name . " - " . $sequence_club[$value->club_id],
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
        return $participant_club;
    }

    public static function getRankedMixTeam($category_detail, $session)
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
            $total_per_point = $total_per_points;
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
                "team" => $value->club_name . " - " . $sequence_club[$value->club_id],
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

        return $participant_club;
    }

    public static function getMedalByPos($pos)
    {
        $output = "";
        $medal_by_pos = [1 => "gold", 2 => "silver", 3 => "bronze"];
        if (isset($medal_by_pos[$pos])) $output = $medal_by_pos[$pos];
        return $output;
    }

    public static function getMedalEliminationByEventId($event_id)
    {
        $participant_team = ArcheryEventParticipant::where("event_id", $event_id)->where("status", 1)->where("type", "team")->get();
        $club_id_join_event = [];
        foreach ($participant_team as $key => $participant) {
            if (in_array($participant->club_id, $club_id_join_event)) {
                continue;
            }
            $club_id_join_event[] = $participant->club_id;
        }


        $clubs = [];
        foreach ($club_id_join_event as $key => $club_id) {
            $category_teams = ArcheryEventCategoryDetail::where("event_id", $event_id)
                ->whereIn("team_category_id", ["male_team", "female_team", "mix_team"])
                ->where("is_join_eliminasi", 1)
                ->get();
            $detail_club = [];
            $gold_medal = 0;
            $silver_medal = 0;
            $bronze_medal = 0;

            foreach ($category_teams as $category) {
                $teams = ArcheryEventEliminationGroupTeams::select("archery_event_elimination_group_teams.*", "archery_event_participants.event_category_id", "archery_event_participants.club_id")
                    ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_elimination_group_teams.participant_id")
                    ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
                    ->where("archery_event_participants.club_id", $club_id)
                    ->where("archery_event_participants.event_category_id", $category->id)
                    ->get();

                foreach ($teams as $key => $team) {
                    $medal_club_per_category = self::getMedalByClub($team->club_id, $category->id);
                    $gold_medal = $gold_medal + $medal_club_per_category["detail_medal"]["gold"];
                    $silver_medal = $silver_medal + $medal_club_per_category["detail_medal"]["silver"];
                    $bronze_medal = $bronze_medal + $medal_club_per_category["detail_medal"]["bronze"];
                }
            }

            $club_medal = [
                "gold" => $gold_medal,
                "silver" => $silver_medal,
                "bronze" => $bronze_medal
            ];


            $detail_club = ArcheryClub::find($club_id);
            if (!$detail_club) {
                throw new BLoCException("club not found");
            }

            $detail_club_with_medal = [
                "club_id" => $detail_club->id,
                "club_name" => $detail_club->name,
                "club_medal" => $club_medal
            ];

            $clubs[] = $detail_club_with_medal;
        }

        return $clubs;
    }

    private static function getMedalByClub($club_id, $category_id)
    {
        $club = ArcheryClub::find($club_id);
        if (!$club) {
            throw new BLoCException("club not found");
        }

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $participant_by_club = ArcheryEventParticipant::where("status", 1)
            ->where("event_category_id", $category_id)
            ->where("club_id", $club_id)
            ->get();

        $gold_medal = 0;
        $silver_medal = 0;
        $bronze_medal = 0;
        foreach ($participant_by_club as $key => $value) {
            $elimination_group_team = ArcheryEventEliminationGroupTeams::where("participant_id", $value->id)->first();
            if ($elimination_group_team) {
                if ($elimination_group_team->elimination_ranked == 1) {
                    $gold_medal = $gold_medal + 1;
                }

                if ($elimination_group_team->elimination_ranked == 2) {
                    $silver_medal = $silver_medal + 1;
                }

                if ($elimination_group_team->elimination_ranked == 3) {
                    $bronze_medal = $bronze_medal + 1;
                }
            }
        }

        $output = [
            "club_id" => $club->id,
            "detail_medal" => [
                "gold" => $gold_medal,
                "silver" => $silver_medal,
                "bronze" => $bronze_medal
            ]
        ];

        return $output;
    }
}
