<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcellCollective extends Model
{
    protected $table = 'excell_collective';
    protected $guarded = ["id"];

    public static function saveExcellCollective(int $user_id, int $event_id, int $city_id, string $url)
    {
        $excellCollective = new ExcellCollective();
        $excellCollective->user_id = $user_id;
        $excellCollective->event_id = $event_id;
        $excellCollective->city_id = $city_id;
        $excellCollective->url = $url;
        $excellCollective->save();
    }
}
