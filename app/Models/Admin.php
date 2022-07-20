<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use App\Models\ArcheryEventOrganizer;
use App\Models\AdminRole;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Models\Provinces;

class Admin extends Model implements JWTSubject, AuthenticatableContract
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
        'name', 'email', 'password', 'city_id', 'province_id', 'phone_number', 'intro', 'eo_id'
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

    protected function getProfile(){
        $admin = Auth::user();
        $admin_role = AdminRole::where("admin_id",$admin->id)->first();
        $admin->role = (object)array(
            "role" => Role::find($admin_role->role_id),
            "event_organizers" => ArcheryEventOrganizer::find($admin->eo_id)
        );
        $city = City::find($admin->city_id);
        $admin->city = [
                        "id" => $city ? $city->id : 0, 
                        "name" => $city ? $city->name : ""
        ];
        $province_id = $city ? $city->province_id : 0;
        $province = Provinces::find($province_id);
        $admin->province_id = $province_id;
        $admin->province = [
                        "id" => $province ? $province->id : 0 , 
                        "name" => $province ? $province->name : ""
        ];
        return $admin;
    }
}