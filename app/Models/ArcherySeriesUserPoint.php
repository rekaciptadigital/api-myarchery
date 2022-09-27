<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventSerie;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySerieCity;
use App\Models\City;
use App\Models\User;
use App\Models\ArcheryScoring;
use App\Models\ArcherySeriesMasterPoint;

class ArcherySeriesUserPoint extends Model
{
    protected $table = 'archery_serie_user_point';
    protected $guarded = ['id'];

    protected function setPoint($member_id, $type, $pos)
    {
        $member = ArcheryEventParticipantMember::find($member_id);
        if (!$member) return false;

        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        if (!$participant) return false;

        $user_id = $participant->user_id;
        $category_id = $participant->event_category_id;
        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) return false;

        $event_serie = ArcheryEventSerie::where("event_id", $category->event_id)->first();
        if (!$event_serie) return false;

        $archerySeriesCategory = ArcherySeriesCategory::where("age_category_id", $category->age_category_id)
            ->where("competition_category_id", $category->competition_category_id)
            ->where("distance_id", $category->distance_id)
            ->where("team_category_id", $category->team_category_id)
            ->where("serie_id", $event_serie->serie_id)
            ->first();
        if (!$archerySeriesCategory) return false;
        $t = 1;
        if ($type == "elimination") {
            $t = 2;
        }

        $point = ArcherySeriesMasterPoint::where("type", $t)->where("serie_id", $event_serie->serie_id)->where("start_pos", "<=", $pos)->where("end_pos", ">=", $pos)->first();
        if (!$point) return false;

        $member_point = $this->where("member_id", $member_id)->where("type", $type)->first();
        // get detail event
        if ($member_point) {
            $member_point->update([
                "point" => $point->point,
                "status" => $member->is_series,
                "position" => $pos,
            ]);
        } else {
            $this->create([
                "event_serie_id" => $event_serie->id,
                "user_id" => $user_id,
                "event_category_id" => $archerySeriesCategory->id,
                "point" => $point->point,
                "status" => $member->is_series,
                "type" => $type,
                "position" => $pos,
                "member_id" => $member_id,
            ]);
        }
    }

    protected function setMemberQualificationPoint($event_category_id)
    {
        $category = ArcheryEventCategoryDetail::find($event_category_id);
        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }
        $pos = 0;
        $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id, 1, $session);
        foreach ($qualification_rank as $key => $value) {
            $pos = $pos + 1;
            $this->setPoint($value["member"]->id, "qualification", $pos);
        }
    }

    protected function setAutoUserMemberCategory($event_id, $user_id = 0)
    {
        $event_serie = ArcheryEventSerie::where("event_id", $event_id)->first();
        if (!$event_serie) return false;
        $success = [];
        $not_set = [];
        $remove = [];
        $list_member_category = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.user_id",
            "archery_event_participants.event_id",
            "archery_serie_categories.id as serie_category_id",
            "archery_event_participant_members.id as member_id"
        )->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("archery_event_series", "archery_event_participants.event_id", "=", "archery_event_series.event_id")
            ->join('archery_serie_categories', function ($join) {
                $join->on("archery_event_participants.age_category_id", "=", "archery_serie_categories.age_category_id")
                    ->on("archery_event_participants.competition_category_id", "=", "archery_serie_categories.competition_category_id")
                    ->on("archery_event_participants.distance_id", "=", "archery_serie_categories.distance_id")
                    ->on("archery_event_participants.team_category_id", "=", "archery_serie_categories.team_category_id");
            })
            ->where(function ($query) use ($user_id) {
                if ($user_id != 0)
                    return $query->where('archery_event_participant_members.user_id', $user_id);
            })
            ->where("archery_event_participants.event_id", $event_id)
            ->where("archery_event_participants.status", 1)->orderBy("archery_event_participant_members.user_id", "DESC")->get();
        foreach ($list_member_category as $key => $value) {
            $check_member_category = ArcheryEventParticipantMember::select(
                "archery_event_participant_members.user_id",
                "archery_event_participants.event_id",
                "archery_serie_categories.id as serie_category_id",
                "archery_event_participant_members.id as member_id"
            )->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                ->join("archery_event_series", "archery_event_participants.event_id", "=", "archery_event_series.event_id")
                ->join('archery_serie_categories', function ($join) {
                    $join->on("archery_event_participants.age_category_id", "=", "archery_serie_categories.age_category_id")
                        ->on("archery_event_participants.competition_category_id", "=", "archery_serie_categories.competition_category_id")
                        ->on("archery_event_participants.distance_id", "=", "archery_serie_categories.distance_id")
                        ->on("archery_event_participants.team_category_id", "=", "archery_serie_categories.team_category_id");
                })->where("archery_event_participant_members.user_id", $value->user_id)
                ->where("archery_event_participants.event_id", $event_id)
                ->where("archery_event_participants.status", 1)->count();
            if ($check_member_category > 1) {
                $not_set[] = "x" . $value->user_id . "\n";
                continue;
            }

            $check_member_join_serie = ArcheryEventParticipantMember::join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                ->where("archery_event_participants.event_id", $event_id)
                ->where("archery_event_participant_members.is_series", 1)
                ->where("archery_event_participants.status", 1)
                ->where("archery_event_participant_members.user_id", $value->user_id)->count();

            $u = User::find($value->user_id);
            $check_serie_city = ArcherySerieCity::where("city_id", $u->address_city_id)->where("serie_id", $event_serie->serie_id)->count();
            if ($check_member_join_serie > 0) {
                $not_set[] = "v" . $value->user_id . "\n";
                if ($check_serie_city < 1 || $u->verify_status != 1) {
                    $remove[] = $value->user_id;

                    ArcheryEventParticipantMember::where("id", $value->member_id)->update([
                        "is_series" => 0
                    ]);

                    ArcherySeriesUserPoint::where("member_id", $value->member_id)->update([
                        "status" => 0
                    ]);
                }
                continue;
            }
            if ($check_serie_city > 0 && $u->verify_status == 1) {
                ArcheryEventParticipantMember::where("id", $value->member_id)->update([
                    "is_series" => 1
                ]);

                ArcherySeriesUserPoint::where("member_id", $value->member_id)->update([
                    "status" => 1
                ]);

                $success[] = $value->user_id;
            }
        }
        error_log("[" . $event_id . "]not set : ==> " . json_encode($not_set));
        error_log("[" . $event_id . "]remove : ==> " . json_encode($remove));
        error_log("[" . $event_id . "]set : ==> " . json_encode($success));
    }

    protected function getUserSeriePointByCategory($category_serie_id)
    {
        $category_series = ArcherySeriesCategory::find($category_serie_id);
        $archery_user_point = ArcherySeriesUserPoint::where("event_category_id", $category_series->id)->where("status", 1)->get();
        $users = [];
        $output = [];
        foreach ($archery_user_point as $key => $value) {
            $member_score_details = isset($users[$value->user_id]) && isset($users[$value->user_id]["score_detail"]) ? $users[$value->user_id]["score_detail"] : ArcheryScoring::ArcheryScoringDetailPoint();
            $member_score_detail_qualification = isset($users[$value->user_id]) && isset($users[$value->user_id]["score_detail_qualification"]) ? $users[$value->user_id]["score_detail_qualification"] : ArcheryScoring::ArcheryScoringDetailPoint();
            if ($value->type == "qualification") {
                $scores = ArcheryScoring::where("participant_member_id", $value->member_id)->where("type", 1)->get();
                foreach ($scores as $s => $score) {
                    $score_details = json_decode($score->scoring_detail);
                    foreach ($score_details as $score_detail) {
                        foreach ($score_detail as $sd) {
                            $member_score_details[$sd->id] = $member_score_details[$sd->id] + 1;
                            $member_score_detail_qualification[$sd->id] = $member_score_detail_qualification[$sd->id] + 1;
                        }
                    }
                }
            } else {
                $scores = ArcheryScoring::where("participant_member_id", $value->member_id)->where("type", 2)->get();
                foreach ($scores as $s => $score) {
                    $score_details = json_decode($score->scoring_detail);
                    foreach ($score_details->shot as $shot) {
                        foreach ($shot->score as $sps) {
                            $member_score_details[$sps] = $member_score_details[$sps] + 1;
                        }
                    }
                }
            }

            $users[$value->user_id]["score_detail"] = $member_score_details;
            $users[$value->user_id]["score_detail_qualification"] = $member_score_detail_qualification;
            $users[$value->user_id]["total_point"] = isset($users[$value->user_id]["total_point"]) ? $users[$value->user_id]["total_point"] + $value->point : $value->point;
            $users[$value->user_id]["point_details"][$value->type] = isset($users[$value->user_id]["point_details"][$value->type]) ? $users[$value->user_id]["point_details"][$value->type] + $value->point : $value->point;
        }

        foreach ($users as $u => $user) {
            $user_detail = User::select("id", "name", "email", "avatar", "address_city_id","date_of_birth")->where("id", $u)->first();
            $city = "";
            $total_score = 0;
            $x_y_qualification = 0;
            foreach ($user["score_detail_qualification"] as $x => $v) {
                if (in_array($x, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, "x"])) {
                    $score_value = $x == "x" ? 10 : $x;
                    $total_score = $total_score + ($score_value * $v);
                    if ($x == "x" || $x == 10)
                        $x_y_qualification = $x_y_qualification + $v;
                }
            }
            if (!empty($user_detail->address_city_id)) {
                $c = City::find($user_detail->address_city_id);
                $city = $c->name;
            }

            $user_profile = [
                "id" => $user_detail->id,
                "name" => $user_detail->name,
                "email" => $user_detail->email,
                "avatar" => $user_detail->avatar,
                "date_of_birth" => $user_detail->date_of_birth,
                "city" => $city,
            ];

            $event_series = ArcheryEventSerie::where("serie_id", $category_series->serie_id)->get();
            $data3 = [];
            foreach ($event_series as $series_event) {
                $output2 = [];
                $evennt = ArcheryEvent::find($series_event->event_id);
                $output2["event_name"] = $evennt->event_name;
                $total_per_series = self::getTotalPointUserPerSeries($user_profile["id"], $category_serie_id, $series_event->id) != [] ? self::getTotalPointUserPerSeries($user_profile["id"], $category_serie_id, $series_event->id) : 0;
                $output2["total"] = isset($total_per_series[$user_profile["id"]]["total_point"]) ? $total_per_series[$user_profile["id"]]["total_point"] : $total_per_series;
                $output2["point_details"] = isset($total_per_series[$user_profile["id"]]["point_details"]) ? $total_per_series[$user_profile["id"]]["point_details"] : $total_per_series;
                $data3[] = $output2;
            }

            $output[] = [
                "tmp_score" => ArcheryScoring::getTotalTmp($user["score_detail_qualification"], $total_score, 0.001),
                "total_point" => $user["total_point"],
                "point_details" => $user["point_details"],
                "total_score_qualification" => $total_score,
                "x_y_qualification" => $x_y_qualification,
                "user" => $user_profile,
                "total_per_series" => $data3
            ];
        }

        usort($output, function ($a, $b) {
            if ($a["total_point"] == $b["total_point"]) {
                return $b["tmp_score"] > $a["tmp_score"] ? 1 : -1;
            }
            if ($a["total_point"] < $b["total_point"]) {
                return 1;
            }
            return -1;
        });

        return $output;
    }

    public static function getTotalPointUserPerSeries($user_id, $category_series_id, $event_series_id)
    {
        $category_series = ArcherySeriesCategory::find($category_series_id);
        $archery_user_point = ArcherySeriesUserPoint::where("event_category_id", $category_series->id)->where("status", 1)->where("event_serie_id", $event_series_id)->where("user_id", $user_id)->get();
        $users = [];
        foreach ($archery_user_point as $key => $value) {
            $member_score_details = isset($users[$value->user_id]) && isset($users[$value->user_id]["score_detail"]) ? $users[$value->user_id]["score_detail"] : ArcheryScoring::ArcheryScoringDetailPoint();
            $member_score_detail_qualification = isset($users[$value->user_id]) && isset($users[$value->user_id]["score_detail_qualification"]) ? $users[$value->user_id]["score_detail_qualification"] : ArcheryScoring::ArcheryScoringDetailPoint();
            if ($value->type == "qualification") {
                $scores = ArcheryScoring::where("participant_member_id", $value->member_id)->where("type", 1)->get();
                foreach ($scores as $s => $score) {
                    $score_details = json_decode($score->scoring_detail);
                    foreach ($score_details as $score_detail) {
                        foreach ($score_detail as $sd) {
                            $member_score_details[$sd->id] = $member_score_details[$sd->id] + 1;
                            $member_score_detail_qualification[$sd->id] = $member_score_detail_qualification[$sd->id] + 1;
                        }
                    }
                }
            } else {
                $scores = ArcheryScoring::where("participant_member_id", $value->member_id)->where("type", 2)->get();
                foreach ($scores as $s => $score) {
                    $score_details = json_decode($score->scoring_detail);
                    foreach ($score_details->shot as $shot) {
                        foreach ($shot->score as $sps) {
                            $member_score_details[$sps] = $member_score_details[$sps] + 1;
                        }
                    }
                }
            }

            $users[$value->user_id]["score_detail"] = $member_score_details;
            $users[$value->user_id]["score_detail_qualification"] = $member_score_detail_qualification;
            $users[$value->user_id]["total_point"] = isset($users[$value->user_id]["total_point"]) ? $users[$value->user_id]["total_point"] + $value->point : $value->point;
            $users[$value->user_id]["point_details"][$value->type] = isset($users[$value->user_id]["point_details"][$value->type]) ? $users[$value->user_id]["point_details"][$value->type] + $value->point : $value->point;
        }
        // return $users;

        $all_category_series = ArcherySeriesCategory::where("serie_id", $category_series->serie_id)->get();

        if ($users == []) {
            $event_series =  ArcheryEventSerie::find($event_series_id);
            $event = ArcheryEvent::find($event_series->event_id);
            $array_cat = [];
            $coun_array_cat = 0;
            foreach ($all_category_series as $acs) {
                $participant_join_series = ArcheryEventParticipant::where("user_id", $user_id)
                    ->where("age_category_id", $acs->age_category_id)
                    ->where("distance_id", $acs->distance_id)
                    ->where("competition_category_id", $acs->competition_category_id)
                    ->where("team_category_id", $acs->team_category_id)
                    ->where("status", 1)
                    ->where("event_id", $event_series->event_id)
                    ->first();

                if ($participant_join_series) {
                    $array_cat[] = $participant_join_series;
                }
            }

            $coun_array_cat = count($array_cat);
            if ($coun_array_cat > 1) {
                $users = "lebih dari satu";
                return $users;
            }

            if ($coun_array_cat == 1) {
                if (
                    $category_series->age_category_id == $array_cat[0]->age_category_id
                    && $category_series->distance_id == $array_cat[0]->distance_id
                    && $category_series->competition_category_id == $array_cat[0]->competition_category_id
                    && $category_series->team_category_id == $array_cat[0]->team_category_id
                ) {
                    $users = "belum tentukan pemeringkatan series";
                    return $users;
                }
            }
        }

        return $users;
    }
}
