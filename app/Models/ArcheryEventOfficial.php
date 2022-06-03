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

    public static function insertOrderOfficial($user_id, $club_id, $archery_event_official_detail, $status = 4)
    {
        return self::create([
            'user_id' => $user_id,
            'club_id' => $club_id,
            'transaction_log_id' => 0,
            'event_official_detail_id' => $archery_event_official_detail,
            'status' => $status
        ]);
    }

    public static function countEventOfficialBooking($archery_event_official_detail_id)
    {
        $time_now = time();

        return ArcheryEventOfficial::select("archery_event_official.*")
            ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_official.transaction_log_id")
            ->where("event_official_detail_id", $archery_event_official_detail_id)
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_official.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    if ("transaction_logs.expired_time" != null) {
                        $q->where("archery_event_official.status", 4);
                        $q->where("transaction_logs.expired_time", ">", $time_now);
                    }
                });
            })->get()->count();
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
                'status' => $archery_event_official->status,
                'status_label' => self::getStatusLabel($archery_event_official->status),
            ];
        }
        return $data;
    }

    public function getListOfficial()
    {
        return $this->relation_with_participant_detail;
    }
}
