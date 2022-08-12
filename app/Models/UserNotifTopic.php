<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotifTopic extends Model
{
    protected $table = 'user_notif_topic';
    protected $fillable = ['topic', 'user_id'];

    protected $primaryKey = "user_id";

    public static function saveTopic($topic, $user_id)
    {
        return self::firstOrNew(array(
            'topic' => $topic,
            'user_id' => $user_id
        ))->save();
    }

}
