<?php

namespace App\Libraries;

use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Models\City;
use App\Models\Provinces;
use DAI\Utils\Exceptions\BLoCException;

class ClubRanked
{

    public static function getEventRanked($event_id, $rules_rating_club = null, $group_category_id = null, $age_category_id = null, $competition_category_id = null, $distance_id = null)
    {
        $output = [];
        $club_or_city_ids = [];
        $cat_detail = [];
        $max_pos = 4;

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        $tag_ranked = "club_id";
        if ($event->with_contingent == 1) {
            $tag_ranked = "city_id";
        }

        // start blok dapatkan medali kualifikasi dan eliminasi individu
        $members = ArcheryEventParticipantMember::select(
            "archery_event_elimination_members.*",
            "archery_event_participants.club_id",
            "archery_event_participants.city_id",
            "archery_event_participants.event_category_id",
            "archery_master_age_categories.label as label_age"
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
            "archery_master_age_categories",
            "archery_master_age_categories.id",
            "=",
            "archery_event_category_details.age_category_id"
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
            ->where("archery_event_participants.status", 1);

        if ($event->with_contingent == 1) {
            $members->join(
                "cities",
                "cities.id",
                "=",
                "archery_event_participants.city_id"
            );
        } else {
            $members->join(
                "archery_clubs",
                "archery_clubs.id",
                "=",
                "archery_event_participants.club_id"
            );
        }

        $members = $members->get();


        $data = ArcheryEventParticipant::select("archery_event_participants.city_id", "archery_event_participants.club_id");

        if ($event->with_contingent == 1) {
            $data = $data->join("cities", "cities.id", "=", "archery_event_participants.city_id");
        } else {
            $data = $data->where("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
        }

        $data = $data->where("archery_event_participants.status", 1)
            ->where("archery_event_participants.event_id", $event->id)
            ->get();

        $list_category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_age_categories.label")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
            ->where("event_id", $event->id)
            ->get();
        foreach ($data as $d_key => $d) {
            $club_or_city_ids[$d[$tag_ranked]] = [];
            $club_or_city_ids[$d[$tag_ranked]]["city_id"] = $d->city_id;
            $club_or_city_ids[$d[$tag_ranked]]["club_id"] = $d->club_id;
            $club_or_city_ids[$d[$tag_ranked]]["total"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["gold"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["silver"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["bronze"] = 0;

            $club_or_city_ids[$d[$tag_ranked]]["individu"]["total"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["individu"]["gold"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["individu"]["silver"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["individu"]["bronze"] = 0;

            $club_or_city_ids[$d[$tag_ranked]]["team"]["total"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["team"]["gold"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["team"]["silver"] = 0;
            $club_or_city_ids[$d[$tag_ranked]]["team"]["bronze"] = 0;

            foreach ($list_category as $lc_key => $lc_value) {
                if (!isset($cat_detail[$lc_value->id])) {
                    $cat_detail[$lc_value->id] = $lc_value;
                }
                $club_or_city_ids[$d[$tag_ranked]]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["gold"] = 0;
                $club_or_city_ids[$d[$tag_ranked]]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["silver"] = 0;
                $club_or_city_ids[$d[$tag_ranked]]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["bronze"] = 0;
                $club_or_city_ids[$d[$tag_ranked]]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["qualification"] = 0;
                $club_or_city_ids[$d[$tag_ranked]]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["elimination"] = 0;
                $club_or_city_ids[$d[$tag_ranked]]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label][$lc_value->team_category_id]["gold"] = 0;
                $club_or_city_ids[$d[$tag_ranked]]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label][$lc_value->team_category_id]["silver"] = 0;
                $club_or_city_ids[$d[$tag_ranked]]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label][$lc_value->team_category_id]["bronze"] = 0;
            }
        }

        foreach ($members as $key => $value) {
            $category_detail = $cat_detail[$value->event_category_id];

            if ($event->with_contingent != 1) {
                $medal_qualification = self::getMedalByPos($value->position_qualification);
                if (!empty($medal_qualification)) {
                    $club_or_city_ids[$value[$tag_ranked]]["total"] = $club_or_city_ids[$value[$tag_ranked]]["total"] + 1;
                    $club_or_city_ids[$value[$tag_ranked]][$medal_qualification] = $club_or_city_ids[$value[$tag_ranked]][$medal_qualification] + 1;
                    $club_or_city_ids[$value[$tag_ranked]]["individu"]["total"] = $club_or_city_ids[$value[$tag_ranked]]["individu"]["total"] + 1;
                    $club_or_city_ids[$value[$tag_ranked]]["individu"][$medal_qualification] = $club_or_city_ids[$value[$tag_ranked]]["individu"][$medal_qualification] + 1;
                    $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$medal_qualification] =
                        $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$medal_qualification] + 1;
                    $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age]["qualification"] =
                        $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age]["qualification"] + 1;
                    $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$category_detail->team_category_id][$medal_qualification] =
                        $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$category_detail->team_category_id][$medal_qualification] + 1;
                }
            }

            $medal_elimination = self::getMedalByPos($value->elimination_ranked);
            if (!empty($medal_elimination)) {
                $club_or_city_ids[$value[$tag_ranked]]["total"] = $club_or_city_ids[$value[$tag_ranked]]["total"] + 1;
                $club_or_city_ids[$value[$tag_ranked]][$medal_elimination] =  $club_or_city_ids[$value[$tag_ranked]][$medal_elimination] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["individu"]["total"] =  $club_or_city_ids[$value[$tag_ranked]]["individu"]["total"] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["individu"][$medal_elimination] = $club_or_city_ids[$value[$tag_ranked]]["individu"][$medal_elimination] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$medal_elimination] =
                    $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$medal_elimination] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age]["elimination"] =
                    $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age]["elimination"] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$category_detail->team_category_id][$medal_elimination] =
                    $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$category_detail->team_category_id][$medal_elimination] + 1;
            }
        }
        // end blok dapatkan medali kualifikasi dan eliminasi individu

        // start blok dapatkan medali kualifikasi dan eliminasi beregu
        $list_category_team = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_age_categories.label as label_age_category")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
            ->where("event_id", $event_id)
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

        foreach ($list_category_team as $t => $team) {
            $elimination_group = ArcheryEventEliminationGroup::where("category_id", $team->id)->first();
            if ($elimination_group) {
                continue;
            }

            $category_detail_male = ArcheryEventCategoryDetail::where("event_id", $team->event_id)
                ->where("age_category_id", $team->age_category_id)
                ->where("competition_category_id", $team->competition_category_id)
                ->where("distance_id", $team->distance_id)
                ->where("team_category_id", "individu male")
                ->first();

            $category_detail_femaie = ArcheryEventCategoryDetail::where("event_id", $team->event_id)
                ->where("age_category_id", $team->age_category_id)
                ->where("competition_category_id", $team->competition_category_id)
                ->where("distance_id", $team->distance_id)
                ->where("team_category_id", "individu female")
                ->first();

            // start dapatin rank kualifikasi beregu

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

                $mix_ranked = ArcheryEventParticipant::mixTeamBestOfThree($team);
                $mix_pos = 0;
                foreach ($mix_ranked as $mr => $mrank) {
                    $mix_pos = $mix_pos + 1;
                    if ($mrank["total"] < 1) {
                        continue;
                    }

                    $medal_mix_team = self::getMedalByPos($mix_pos);
                    if (!empty($medal_mix_team)) {
                        $club_or_city_ids[$mrank[$tag_ranked]]["total"] = $club_or_city_ids[$mrank[$tag_ranked]]["total"] + 1;
                        $club_or_city_ids[$mrank[$tag_ranked]][$medal_mix_team] = $club_or_city_ids[$mrank[$tag_ranked]][$medal_mix_team] + 1;
                        $club_or_city_ids[$mrank[$tag_ranked]]["team"]["total"] = $club_or_city_ids[$mrank[$tag_ranked]]["team"]["total"] + 1;
                        $club_or_city_ids[$mrank[$tag_ranked]]["team"][$medal_mix_team] = $club_or_city_ids[$mrank[$tag_ranked]]["team"][$medal_mix_team] + 1;
                        $club_or_city_ids[$mrank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category][$medal_mix_team] =
                            $club_or_city_ids[$mrank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category][$medal_mix_team] + 1;
                        $club_or_city_ids[$mrank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category]["qualification"] =
                            $club_or_city_ids[$mrank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category]["qualification"] + 1;
                        $club_or_city_ids[$mrank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category][$team->team_category_id][$medal_mix_team] =
                            $club_or_city_ids[$mrank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category][$team->team_category_id][$medal_mix_team] + 1;
                    }

                    if ($mix_pos >= 3) {
                        break;
                    }
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

                $ranked = ArcheryEventParticipant::teamBestOfThree($team);;
                $pos = 0;
                foreach ($ranked as $r => $rank) {
                    $pos = $pos + 1;
                    if ($rank["total"] < 1) {
                        continue;
                    }

                    $medal_team = self::getMedalByPos($pos);
                    if (!empty($medal_team)) {
                        $club_or_city_ids[$rank[$tag_ranked]]["total"] = $club_or_city_ids[$rank[$tag_ranked]]["total"] + 1;
                        $club_or_city_ids[$rank[$tag_ranked]][$medal_team] = $club_or_city_ids[$rank[$tag_ranked]][$medal_team] + 1;
                        $club_or_city_ids[$rank[$tag_ranked]]["team"]["total"] = $club_or_city_ids[$rank[$tag_ranked]]["team"]["total"] + 1;
                        $club_or_city_ids[$rank[$tag_ranked]]["team"][$medal_team] = $club_or_city_ids[$rank[$tag_ranked]]["team"][$medal_team] + 1;
                        $club_or_city_ids[$rank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category][$medal_team]
                            = $club_or_city_ids[$rank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category][$medal_team] + 1;
                        $club_or_city_ids[$rank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category]["qualification"]
                            = $club_or_city_ids[$rank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category]["qualification"] + 1;
                        $club_or_city_ids[$rank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category][$team->team_category_id][$medal_team]
                            = $club_or_city_ids[$rank[$tag_ranked]]["detail_medal"]["category"][$team->competition_category_id][$team->label_age_category][$team->team_category_id][$medal_team] + 1;
                    }

                    if ($pos >= 3) {
                        break;
                    }
                }
            }

            // end dapatkan rank kualifikasi beregu
        }
        // dapatkan data eliminasi beregu
        $group = ArcheryEventParticipant::select(
            "archery_event_elimination_group_teams.*",
            "archery_event_participants.club_id",
            "archery_event_participants.city_id",
            "archery_event_participants.event_category_id",
            "archery_master_age_categories.label as label_age"
        )->join(
            "archery_event_elimination_group_teams",
            "archery_event_participants.id",
            "=",
            "archery_event_elimination_group_teams.participant_id"
        )->join(
            "archery_event_category_details",
            "archery_event_category_details.id",
            "=",
            "archery_event_participants.event_category_id"
        )->join(
            "archery_master_age_categories",
            "archery_master_age_categories.id",
            "=",
            "archery_event_category_details.age_category_id"
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
            ->where("archery_event_participants.status", 1);

        if ($event->with_contingent == 1) {
            $group->join(
                "cities",
                "cities.id",
                "=",
                "archery_event_participants.city_id"
            );
        } else {
            $group->join(
                "archery_clubs",
                "archery_clubs.id",
                "=",
                "archery_event_participants.club_id"
            );
        }

        $group = $group->get();

        foreach ($group as $key => $value) {
            $category_detail = $cat_detail[$value->event_category_id];

            $medal_elimination = self::getMedalByPos($value->elimination_ranked);
            if (!empty($medal_elimination)) {
                $club_or_city_ids[$value[$tag_ranked]]["total"] = $club_or_city_ids[$value[$tag_ranked]]["total"] + 1;
                $club_or_city_ids[$value[$tag_ranked]][$medal_elimination] = $club_or_city_ids[$value[$tag_ranked]][$medal_elimination] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["team"]["total"] = $club_or_city_ids[$value[$tag_ranked]]["team"]["total"] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["team"][$medal_elimination] = $club_or_city_ids[$value[$tag_ranked]]["team"][$medal_elimination] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$medal_elimination]
                    = $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$medal_elimination] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age]["elimination"]
                    = $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age]["elimination"] + 1;
                $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$category_detail->team_category_id][$medal_elimination]
                    = $club_or_city_ids[$value[$tag_ranked]]["detail_medal"]["category"][$category_detail->competition_category_id][$value->label_age][$category_detail->team_category_id][$medal_elimination] + 1;
            }
        }

        if ($event->with_contingent == 1) {
            $list_city = City::where("province_id", $event->province_id)->get();
            foreach ($list_city as $lcity_key => $lcity_value) {
                if (!isset($club_or_city_ids[$lcity_value->id])) {
                    $club_or_city_ids[$lcity_value->id] = [];
                    $club_or_city_ids[$lcity_value->id]["city_id"] = $lcity_value->id;
                    $club_or_city_ids[$lcity_value->id]["club_id"] = 0;
                    $club_or_city_ids[$lcity_value->id]["total"] = 0;
                    $club_or_city_ids[$lcity_value->id]["gold"] = 0;
                    $club_or_city_ids[$lcity_value->id]["silver"] = 0;
                    $club_or_city_ids[$lcity_value->id]["bronze"] = 0;

                    $club_or_city_ids[$lcity_value->id]["individu"]["total"] = 0;
                    $club_or_city_ids[$lcity_value->id]["individu"]["gold"] = 0;
                    $club_or_city_ids[$lcity_value->id]["individu"]["silver"] = 0;
                    $club_or_city_ids[$lcity_value->id]["individu"]["bronze"] = 0;

                    $club_or_city_ids[$lcity_value->id]["team"]["total"] = 0;
                    $club_or_city_ids[$lcity_value->id]["team"]["gold"] = 0;
                    $club_or_city_ids[$lcity_value->id]["team"]["silver"] = 0;
                    $club_or_city_ids[$lcity_value->id]["team"]["bronze"] = 0;

                    foreach ($list_category as $lc_key => $lc_value) {
                        if (!isset($cat_detail[$lc_value->id])) {
                            $cat_detail[$lc_value->id] = $lc_value;
                        }
                        $club_or_city_ids[$lcity_value->id]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["gold"] = 0;
                        $club_or_city_ids[$lcity_value->id]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["silver"] = 0;
                        $club_or_city_ids[$lcity_value->id]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["bronze"] = 0;
                        $club_or_city_ids[$lcity_value->id]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["qualification"] = 0;
                        $club_or_city_ids[$lcity_value->id]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label]["elimination"] = 0;
                        $club_or_city_ids[$lcity_value->id]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label][$lc_value->team_category_id]["gold"] = 0;
                        $club_or_city_ids[$lcity_value->id]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label][$lc_value->team_category_id]["silver"] = 0;
                        $club_or_city_ids[$lcity_value->id]["detail_medal"]["category"][$lc_value->competition_category_id][$lc_value->label][$lc_value->team_category_id]["bronze"] = 0;
                    }
                }
            }
        }

        // end blok dapatkan medali kualifikasi dan eliminasi beregu
        foreach ($club_or_city_ids as $k => $v) {
            // club
            $club = ArcheryClub::find($k);
            $club_name = "";
            $club_logo = "";
            $club_city_name = "";
            $club_id = "";
            if ($club) {
                $club_name = $club->name;
                $club_logo = $club->logo;
                $city_club = City::find($club->city);
                $club_id = $club->id;
                if ($city_club) {
                    $club_city_name = $city_club->name;
                }
            }

            // city
            $contingent = City::find($v["city_id"]);
            $contingent_name = "";
            $contingent_logo = "";
            $contingent_id = "";
            if ($contingent) {
                $contingent_name = $contingent->name;
                $contingent_logo = $contingent->logo;
                $contingent_id = $contingent->id;
            }

            $total_gold = $v["gold"];
            $total_silver = $v["silver"];
            $total_bronze = $v["bronze"];
            $output[] = [
                "with_contingent" => $event->with_contingent,
                "club_name" => $club_name,
                "club_id" => $club_id,
                "contingent_id" => $contingent_id,
                "club_logo" => $club_logo,
                "club_city" => $club_city_name,
                "contingent_name" => $contingent_name,
                "contingent_logo" => $contingent_logo,
                "detail_medal" => $v["detail_medal"],
                "gold" => $total_gold,
                "silver" => $total_silver,
                "bronze" => $total_bronze,
                "total" => $total_gold + $total_silver + $total_bronze,
                "detail_modal_by_group" => [
                    "indiividu" => $v["individu"],
                    "team" => $v["team"],
                ]
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

    public static function getMedalByPos($pos)
    {
        $output = "";
        $medal_by_pos = [
            1 => "gold",
            2 => "silver",
            3 => "bronze"
        ];
        if (isset($medal_by_pos[$pos])) {
            $output = $medal_by_pos[$pos];
        }
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
