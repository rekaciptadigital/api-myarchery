<?php

namespace App\Models;

use App\Jobs\AccountVerificationJob;
use Queue;
use DAI\Utils\Exceptions\BLoCException;
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
        'name', 'email', 'password', 'date_of_birth', 'phone_number',
        'gender', 'verify_status', 'address', 'place_of_birth',
        'address_province_id', 'address_city_id', "email_verified"
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

    public static function getDetailUser($user_id)
    {
        $data = [];
        $user = User::find($user_id);
        if ($user) {
            $data = [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar' => $user->avatar,
                'date_of_birth' => $user->date_of_birth,
                'age' => $user->age,
                'gender' => $user->gender,
                'address' => $user->address,
                "can_update_name" => $user->can_update_name,
                "can_update_date_of_birth" => $user->can_update_date_of_birth,
                "can_update_gender" => $user->can_update_gender
            ];
        }
        return $data;
    }

    public function getDataVerifikasiUser()
    {
        return [
            "user_id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "nik" => $this->nik,
            "ktp_kk" => $this->ktp_kk,
            "address" => $this->address,
            "address_province_id" => $this->address_province_id,
            "detail_province" => Provinces::getDetailProvince($this->address_province_id),
            "address_city_id" => $this->address_city_id,
            "detail_city" => City::getDetailCity($this->address_city_id),
            "passport_number" => $this->passport_number,
            "is_wna" => $this->is_wna,
            "country_id" => $this->country_id,
            "city_of_country_id" => $this->city_of_country_id,
            "passport_img" => $this->passport_img,
            "detail_country" => Country::getDetailCountry($this->country_id),
            "detail_city_country" => CityCountry::getDetailCityCountry($this->city_of_country_id)
        ];
    }

    public function checkIsCompleteUserData()
    {
        $is_complete = 0;
        if ($this->gender && $this->address && $this->date_of_birth) {
            if ($this->is_wna == 1) {
                if (
                    $this->passport_number
                    && $this->country_id
                    && $this->city_of_country_id
                    && $this->passport_img
                ) {
                    $is_complete = 1;
                }
            } else {
                if (
                    $this->nik
                    && $this->address_province_id
                    && $this->address_city_id
                    && $this->ktp_kk
                ) {
                    $is_complete = 1;
                }
            }
        }

        return $is_complete;
    }

    public static function sendOtpAccountVerification($user_id)
    {
        $user = User::find($user_id);
        $code = substr(str_shuffle('1234567890'), 0, 5);

        $otp_code = OtpVerificationCode::where("email", $user->email)->where("expired_time", ">", time())->get();
        if ($otp_code->count() >= 3) {
            throw new BLoCException("anda sudah melewati batas maksimal pengiriman otp, periksa kembali email anda");
        }
        $otp_code = new OtpVerificationCode();
        $otp_code->user_id = $user->id;
        $otp_code->email = $user->email;
        $otp_code->otp_code = $code;
        $otp_code->expired_time = strtotime("+1 day", time());
        $otp_code->save();

        Queue::push(new AccountVerificationJob([
            "code" => $code,
            "email" => $user->email,
            "name" => $user->name,
        ]));

        return $otp_code;
    }
}
