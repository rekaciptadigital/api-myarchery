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

    protected $status_label = [
        "4" => "Menunggu Pembayaran",
        "3" => "Gagal",
        "2" => "Kadaluarsa",
        "1" => "Diikuti"
    ];

    protected $table = 'archery_event_official';
    protected $guarded = ['id'];

    public static function insertOrderOfficial($user_id, $club_id, $relation_id, $label, $event_official_detail_id, $status = 4)
    {
        return self::create([
            'user_id' => $user_id,
            'club_id' => $club_id,
            'relation_with_participant' => $relation_id,
            'relation_with_participant_label' =>  $label,
            'transaction_log_id' => 0,
            'event_official_detail_id' => $event_official_detail_id,
            'status' => $status
        ]);
    }

    public static function countEventOfficialBooking($archery_event_official_detail_id)
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
            })->count();
    }

    public function getStatusLabel($status_id){
        return isset($this->status_label[$status_id]) ? $this->status_label[$status_id] : "none"; 
    }
}
