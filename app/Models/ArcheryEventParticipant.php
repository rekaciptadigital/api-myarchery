<?php

namespace App\Models;

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
                        ->leftJoin('transaction_logs','transaction_logs.id','archery_event_participants.transaction_log_id')
                        ->get();
    foreach(array($count_participant) as $key => $count){
      $total=$count[0]['total'];
    }

    return $total;
  }

  public static function insertParticipant(
    $user,
    $unique_id,
    $team_name,
    $event_category_detail,
    $status,
    $club_id
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
      'team_name' => $team_name
    ]);
  }
}
