<?php

namespace App\Models;

use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ArcheryEventParticipant extends Model
{
  protected $guarded = ['id'];

  public static $user, $unique_id, $team_name,
    $event_category_detail, $status, $club_id;

  public function archeryEventParticipantMembers()
  {
    return $this->hasMany(ArcheryEventParticipantMember::class, 'archery_event_participant_id', 'id');
  }

  public static function getMemberByUserId($user_id, $participant_id)
  {
    $archery_participant = DB::table('archery_event_participants')
      ->select('archery_event_participant_members.*', 'archery_event_participants.event_id', 'archery_event_participants.event_category_id')
      ->join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
      ->where('archery_event_participant_members.user_id', $user_id)
      ->where('archery_event_participants.id', $participant_id)
      ->where('archery_event_participants.status', 1)
      ->first();
    return $archery_participant;
  }


  public static function getTotalPartisipantByEventByCategory($category_detail_id)
  {
    $count_participant = ArcheryEventParticipant::select(DB::raw("count(if(archery_event_participants.status=1,1,if(FROM_UNIXTIME(transaction_logs.expired_time)>=now(),1,NULL))) as total "))
      ->where('event_category_id', $category_detail_id)
      ->leftJoin('transaction_logs', 'transaction_logs.id', 'archery_event_participants.transaction_log_id')
      ->get();
    foreach (array($count_participant) as $key => $count) {
      $total = $count[0]['total'];
    }

    return $total;
  }

  public static function checkParticipantMixteamOrder($event_id, $age_category_id, $competition_category_id, $distance_id, $club_id, $count_participant_same_category)
  {
    $check_individu_category_detail_male = ArcheryEventCategoryDetail::where('event_id', $event_id)
      ->where('age_category_id', $age_category_id)
      ->where('competition_category_id', $competition_category_id)
      ->where('distance_id', $distance_id)
      ->where('team_category_id', "individu male")
      ->first();

    $check_individu_category_detail_female = ArcheryEventCategoryDetail::where('event_id', $event_id)
      ->where('age_category_id', $age_category_id)
      ->where('competition_category_id', $competition_category_id)
      ->where('distance_id', $distance_id)
      ->where('team_category_id', "individu female")
      ->first();

    if (!$check_individu_category_detail_male || !$check_individu_category_detail_female) {
      throw new BLoCException("kategori individu untuk kategori ini tidak tersedia");
    }

    $check_participant_male = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
      ->where("archery_event_participants.status", 1)
      ->where('archery_event_participants.event_category_id', $check_individu_category_detail_male->id)
      ->where('archery_event_participants.club_id', $club_id)
      ->count();

    $check_participant_female = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
      ->where("archery_event_participants.status", 1)
      ->where('archery_event_participants.event_category_id', $check_individu_category_detail_female->id)
      ->where('archery_event_participants.club_id', $club_id)
      ->count();

    $message_error = "untuk pendaftaran ke " . ($count_participant_same_category + 1) . " minimal harus ada " . (($count_participant_same_category + 1) * 1) . " peserta laki-laki dan peserta perempuan tedaftar dengan club ini pada kategori individu";

    if ($check_participant_male < (($count_participant_same_category + 1) * 1)) {
      throw new BLoCException($message_error);
    }

    if ($check_participant_female < (($count_participant_same_category + 1) * 1)) {
      throw new BLoCException($message_error);
    }
  }

  public static function getTotalPartisipantEventByStatus($category_detail_id, $status = 0)
  {
    return ArcheryEventParticipant::select("archery_event_participants.*", "transaction_logs.order_id", "archery_event_participants.status", "transaction_logs.expired_time")
      ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
      ->where('archery_event_participants.event_category_id', $category_detail_id)
      ->where(function ($query) use ($status) {
        if (!is_null($status) && $status != 0) {
          $query->where('archery_event_participants.status', $status);
          if ($status == 2) {
            $query->orWhere(function ($query) use ($status) {
              $query->where("transaction_logs.status", 4);
              $query->where("transaction_logs.expired_time", "<=", time());
            });
          }
          if ($status == 1) {
            $query->orWhere(function ($query) use ($status) {
              $query->where("archery_event_participants.status", 1);
            });
          }
          if ($status == 4) {
            $query->where("transaction_logs.expired_time", ">=", time());
          }
        }
      })
      ->count();
  }

  public static function countEventUserBooking($event_category_detail_id)
  {
    $time_now = time();

    return ArcheryEventParticipant::leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
      ->where("event_category_id", $event_category_detail_id)
      ->where(function ($query) use ($time_now) {
        $query->where("archery_event_participants.status", 1);
        $query->orWhere(function ($q) use ($time_now) {
          $q->where("archery_event_participants.status", 4);
          $q->where("transaction_logs.status", 4);
          $q->where("transaction_logs.expired_time", ">", $time_now);
        });
        $query->orWhere(function ($q) use ($time_now) {
          $q->where("archery_event_participants.status", 6);
          $q->where("archery_event_participants.expired_booking_time", ">", $time_now);
        });
      })->count();
  }

  public static function insertParticipant(
    $user,
    $unique_id,
    $event_category_detail,
    $status,
    $club_id,
    $day_choice,
    $expired_booking_time = 0,
    $is_early_bird_payment = 0
  ) {
    return self::create([
      'club_id' => $club_id,
      'user_id' => $user->id,
      'status' => $status,
      'event_id' => $event_category_detail->event_id,
      'name' => $user->name,
      'type' => $event_category_detail->category_team,
      'email' => $user->email,
      'phone_number' => $user->phone_number,
      'age' => $user->age,
      'gender' => $user->gender,
      'team_category_id' => $event_category_detail->team_category_id,
      'age_category_id' => $event_category_detail->age_category_id,
      'competition_category_id' => $event_category_detail->competition_category_id,
      'distance_id' => $event_category_detail->distance_id,
      'transaction_log_id' => 0,
      'unique_id' => $unique_id,
      'event_category_id' => $event_category_detail->id,
      'day_choice' => $day_choice,
      "expired_booking_time" => $expired_booking_time,
      "is_early_bird_payment" => $is_early_bird_payment
    ]);
  }

  public static function getQualification($category_id)
  {
    $score_type = 1;
    $name = null;
    $category = ArcheryEventCategoryDetail::find($category_id);
    if (!$category) {
      throw new BLoCException("category not found");
    }

    $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
    if (!$team_category) {
      throw new BLoCException("team category not found");
    }

    $event = ArcheryEvent::find($category->event_id);
    if (!$event) throw new BLoCException("CATEGORY INVALID");

    $session = [];
    for ($i = 0; $i < $category->session_in_qualification; $i++) {
      $session[] = $i + 1;
    }

    if ($category->category_team == "Individual") {
      // $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->getListMemberScoringIndividual($category_detail->id, $score_type, $session, $name, $event->id);
      $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category->id, $score_type, $session, false, $name);

      return $qualification_member;
    }

    if (strtolower($team_category->type) == "team") {
      if ($team_category->id == "mix_team") {
        $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->mixTeamBestOfThree($category, $team_category, $session);
      } else {
        $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->teamBestOfThree($category, $team_category, $session);
      }
    }

    return $data;
  }

  // digunakan untuk mendapatkan data qualification atau elimination dari peringkat satu sampai 3
  public static function getData($category_detail_id, $type, $event_id)
  {
    $data_report = [];
    $category_id = null;
    $elimination_rank = 0;

    $members = ArcheryEventEliminationMember::select("*", "archery_event_category_details.id as category_details_id", "archery_event_participant_members.id as participant_member_id", DB::RAW('date(archery_event_elimination_members.created_at) as date'))
      ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'archery_event_elimination_members.member_id')
      ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
      ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'archery_event_participants.event_category_id')
      ->where("archery_event_category_details.id", $category_detail_id)
      ->where("archery_event_participants.event_id", $event_id)
      ->where(function ($query) use ($type) {
        if ($type == "elimination") {
          $query->where("archery_event_elimination_members.elimination_ranked", '>', 0);
          $query->where("archery_event_elimination_members.elimination_ranked", '<=', 3);
          $query->orderBy('archery_event_elimination_members.elimination_ranked', 'ASC');
        } else if ($type == "qualification") {
          $query->where("archery_event_elimination_members.position_qualification", '>', 0);
          $query->where("archery_event_elimination_members.position_qualification", '<=', 3);
          $query->orderBy('archery_event_elimination_members.position_qualification', 'ASC');
        } else {
          $query->orderBy('archery_event_elimination_members.position_qualification', 'ASC');
        }
      })
      ->orderBy('archery_event_participants.event_category_id', 'ASC')
      ->orderBy('archery_event_category_details.team_category_id', 'DESC')
      ->get();


    if ($members) {
      foreach ($members as $member) {

        $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($member->category_details_id);

        // if ($member->elimination_ranked == 1 || $member->position_qualification == 1) {
        //     $medal = 'Gold';
        // } else if ($member->elimination_ranked == 2 || $member->position_qualification == 2) {
        //     $medal = 'Silver';
        // } else {
        //     $medal = 'Bronze';
        // }

        if ($type == "elimination") {
          $elimination_rank = $member->elimination_ranked;
          if ($member->elimination_ranked == 1) {
            $medal = 'Gold';
          } else if ($member->elimination_ranked == 2) {
            $medal = 'Silver';
          } else {
            $medal = 'Bronze';
          }
        } elseif ($type == "qualification") {
          if ($member->position_qualification == 1) {
            $medal = 'Gold';
          } else if ($member->position_qualification == 2) {
            $medal = 'Silver';
          } else {
            $medal = 'Bronze';
          }
        } else {
          $medal = '-';
        }

        $athlete = $member->name;
        $date = $member->date;

        $club = ArcheryClub::find($member->club_id);
        if (!$club) {
          $club = '';
        } else {
          $club = $club->name;
        }

        $category = ArcheryEventCategoryDetail::find($member->category_details_id);
        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
          $session[] = $i + 1;
        }
        $scoring = ArcheryScoring::generateScoreBySession($member->participant_member_id, 1, $session);

        $data_report[] = array(
          "athlete" => $athlete,
          "club" => $club,
          "category" => $categoryLabel,
          "medal" => $medal,
          "date" => $date,
          "scoring" => $scoring,
          "elimination_rank" => $elimination_rank,
          "participant_id" => $member->archery_event_participant_id
        );

        $category_id = $member->category_details_id;
      }
    }

    if ($type == "elimination") {
      $sorted_data = collect($data_report)->sortBy('elimination_rank')->values()->all();
      return array($sorted_data, $category_id);
    }

    $sorted_data = collect($data_report)->sortByDesc('scoring.total')->values()->all();

    return array($sorted_data, $category_id);
  }

  public static function getDataEliminationTeam($category_detail_id)
  {
    $elimination_group = ArcheryEventEliminationGroup::where('category_id', $category_detail_id)->first();
    if ($elimination_group) {
      $elimination_group_match = ArcheryEventEliminationGroupMatch::select(DB::RAW('distinct group_team_id as teamid'))->where('elimination_group_id', $elimination_group->id)->get();

      $data = array();
      foreach ($elimination_group_match as $key => $value) {

        $elimination_group_team = ArcheryEventEliminationGroupTeams::where('id', $value->teamid)->first();


        if ($elimination_group_team) {
          if ($elimination_group_team->elimination_ranked <= 3) {
            $participant = ArcheryEventParticipant::select("archery_clubs.name as club_name")
              ->where("archery_event_participants.id", $elimination_group_team->participant_id)
              ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
              ->first();
            $club_name = "";
            if ($participant) {
              $club_name = $participant->club_name;
            }
            $data[] = [
              'id' => $elimination_group_team->id,
              'participant_id' => $elimination_group_team->participant_id,
              'club_name' => $club_name,
              'team_name' => $elimination_group_team->team_name,
              'elimination_ranked' => $elimination_group_team->elimination_ranked ?? 0,
              'category' => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail_id),
              'date' => $elimination_group->created_at->format('Y-m-d'),
              "member_team" => ArcheryEventEliminationGroupMemberTeam::select("users.name", "archery_event_participant_members.id as member_id")->where("participant_id", $elimination_group_team->participant_id)
                ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_elimination_group_member_team.member_id")
                ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
                ->get()
            ];
          } else {
            continue;
          }
        }
      }

      $sorted_data = collect($data)->sortBy('elimination_ranked')->values()->take(3);
      return $sorted_data;
    }
  }
}
