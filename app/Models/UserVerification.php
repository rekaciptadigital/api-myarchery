<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    protected $fillable = [
        'user_id',
        'verification_token',
        'expired_at',
        'count_incorrect',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
