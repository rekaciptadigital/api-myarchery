<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable;
    // use Authenticatable, Authorizable;
    // use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'date_of_birth', 'phone_number', 'gender', 'verify_status', 'address', 'place_of_birth', 'address_province_id', 'address_city_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function userArcheryInfo()
    {
        return $this->hasOne(UserArcheryInfo::class);
    }

    protected $appends = ['age', 'status_verify'];

    public function getAgeAttribute()
    {
        $today = Carbon::today('Asia/jakarta');
        return $this->attributes['age'] = $today->diffInYears($this->date_of_birth);
    }

    public function getStatusVerifyAttribute()
    {
        $verify_status = $this->verify_status;
        $status = "Belum terverifikasi";
        if ($verify_status == 3) {
            $status = "Diajukan";
        } else if ($verify_status == 2) {
            $status = "Ditolak";
        } elseif ($verify_status == 1) {
            $status = "Terverifikasi";
        } else {
            $status = "Belum terverifikasi";
        }

        return $this->attributes['status_verify'] = $status;
    }
}
