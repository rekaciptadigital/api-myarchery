<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ChildrenClassificationMembers;
use App\Models\Country;
use App\Models\ProvinceCountry;
use App\Models\CityCountry;
use App\Models\ArcheryClub;
use App\Models\City;
use App\Models\Provinces;

class ClassificationEventRegisters extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = [
        'detail_classification_children', 'detail_classification_country', 'detail_classification_provincy', 'detail_classification_city', 'detail_classification_archery_club'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:m:s',
        'updated_at' => 'datetime:Y-m-d H:m:s'
    ];

    public function getDetailClassificationChildrenAttribute()
    {
        $response = [];
        $parent = ChildrenClassificationMembers::find($this->children_classification_id);

        if ($parent) {
            $response["id"] = $parent->id;
            $response["title"] = $parent->title;
        }

        return $this->attributes['detail_classification_children'] = $response;
    }

    public function getDetailClassificationCountryAttribute()
    {
        $response = [];
        $data = Country::find($this->country_id);

        if ($data) {
            $response["id"] = $data->id;
            $response["name"] = $data->name;
        }

        return $this->attributes['detail_classification_country'] = $response;
    }

    public function getDetailClassificationProvincyAttribute()
    {
        $response = [];
        $data = false;

        if ($this->country_id == 102 || empty($this->country_id)) {
            $data = Provinces::find($this->provinsi_id);
        } else {
            $data = ProvinceCountry::find($this->provinsi_id);
        }

        if ($data) {
            $response["id"] = $data->id;
            $response["name"] = $data->name;
        }

        return $this->attributes['detail_classification_provincy'] = $response;
    }

    public function getDetailClassificationCityAttribute()
    {
        $response = [];
        // $data = CityCountry::find($this->city_id);

        $data = false;

        if ($this->country_id == 102 || empty($this->country_id)) {
            $data = City::find($this->city_id);
        } else {
            $data = CityCountry::find($this->city_id);
        }

        if ($data) {
            $response["id"] = $data->id;
            $response["name"] = $data->name;
        }

        return $this->attributes['detail_classification_city'] = $response;
    }

    public function getDetailClassificationArcheryClubAttribute()
    {
        $response = [];
        $data = ArcheryClub::find($this->archery_club_id);

        if ($data) {
            $response["id"] = $data->id;
            $response["name"] = $data->name;
        }

        return $this->attributes['detail_classification_archery_club'] = $response;
    }
}
