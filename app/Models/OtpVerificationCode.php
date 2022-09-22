<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerificationCode extends Model
{
    protected $table = 'otp_verification_code';
    protected $guarded = ["id"];
}
