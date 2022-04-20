<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventOfficial extends Model
{
    protected $relation_with_participant_detail = [
        '1' => 'Pelatih',
        '2' => 'Manager Club/Tim',
        '3' => 'Orang Tua',
        '4' => 'Saudara',
        '0' => 'Lainnya'
    ];

    protected static $status_label = [
        "4" => "Menunggu Pembayaran",
        "3" => "Gagal",
        "2" => "Kadaluarsa",
        "1" => "Diikuti"
    ];

    protected $table = 'archery_event_official';
    protected $guarded = ['id'];

    public static function insertOrderOfficial($user_id, $club_id, $team_category_id, $age_category_id, $competition_category_id, $distance_id,$archery_event_official_detail, $status = 4)
    {
        return self::create([
            'user_id' => $user_id,
            'club_id' => $club_id,
            'team_category_id' => $team_category_id,
            'age_category_id' =>  $age_category_id,
            'competition_category_id' => $competition_category_id,
            'distance_id' =>  $distance_id,
            'transaction_log_id' => 0,
            'event_official_detail_id' => $archery_event_official_detail,
            'status' => $status
        ]);
    }

    public static function countEventOfficialBooking($archery_event_official_detail_id,$club_id="")
    {
        $time_now = time();

        return ArcheryEventOfficial::leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_official.transaction_log_id")
            ->where("event_official_detail_id", $archery_event_official_detail_id)
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_official.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("archery_event_official.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            })
            ->where(function ($query) use ($club_id) {
                if (!empty($event_name)) {
                    $query->where('archery_events.event_name', 'like', '%' . $event_name . '%');
                }
            })->count();
    }

    protected static function getStatusLabel($status_id)
    {
        return isset(self::$status_label[$status_id]) ? self::$status_label[$status_id] : "none";
    }

    public static function getDetailEventOfficialById($event_official_id)
    {
        $data = [];
        $archery_event_official = ArcheryEventOfficial::find($event_official_id);
        if ($archery_event_official) {
            $data = [
                'event_official_id' => $archery_event_official->id,
                'type' => $archery_event_official->type,
                'relation_with_participant' => $archery_event_official->relation_with_participant,
                'relation_with_participant_label' => $archery_event_official->relation_with_participant_label,
                'status' => $archery_event_official->status,
                'status_label' => self::getStatusLabel($archery_event_official->status),
                'team_category_id' => $archery_event_official->team_category_id,
                'category-label'=>$archery_event_official->team_category_id."-".$archery_event_official->age_category_id."-".$archery_event_official->competition_category_id."-".$archery_event_official->distance_id."m"
            ];
        }
        return $data;
    }

    public function getListOfficial(){
        return $this->relation_with_participant_detail;
    }
}
