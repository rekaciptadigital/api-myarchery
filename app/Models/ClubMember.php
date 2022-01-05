<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubMember extends Model
{
    protected $table = 'archery_club_members';
    protected $fillable = ['club_id', 'user_id', 'status', 'role'];

    public static $user_id;
    public static $club_id;
    public static $role;
    public static $status;

    public static function addNewMember($club_id, $user_id, $status, $role)
    {
        return self::create([
            'club_id' => $club_id,
            'user_id' => $user_id,
            'status' => $status,
            'role' => $role
        ]);
    }

    public static function getStatus($club_id, $user_id)
    {
       $data = self::where('club_id', $club_id)->where('user_id', $user_id)->first();
       if (!$data) {
           return 0;
       }
       return 1;
    }
}
