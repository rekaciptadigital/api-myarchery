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
    $expired_booking_time = 0
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
      "expired_booking_time" => $expired_booking_time
    ]);
  }
}
