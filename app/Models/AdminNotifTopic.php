<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotifTopic extends Model
{
    protected $table = 'admin_notif_topic';
    protected $fillable = ['topic', 'admin_id'];

    protected $primaryKey = "admin_id";

    public static function saveTopic($topic, $admin_id)
    {
        return self::firstOrNew(array(
            'topic' => $topic,
            'admin_id' => $admin_id
        ))->save();
    }
}
