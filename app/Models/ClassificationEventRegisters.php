<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ParentClassificationMembers;
use App\Models\ChildrenClassificationMembers;
use App\Models\Country;
use App\Models\ProvinceCountry;
use App\Models\CityCountry;
use App\Models\ArcheryClub;

class ClassificationEventRegisters extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = [
        'detail_parent_classification', 'detail_children_classification', 'detail_country', 'detail_provincy', 'detail_city', 'detail_club'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:m:s',
        'updated_at' => 'datetime:Y-m-d H:m:s'
    ];

    public function getDetailParentClassificationAttribute()
    {
        $response = [];
        $parent = ParentClassificationMembers::find($this->parent_classification_id);

        if ($parent) {
            $response["id"] = $parent->id;
            $response["title"] = $parent->title;
        }

        return $this->attributes['detail_parent_classification'] = $response;
    }

    public function getDetailChildrenClassificationAttribute()
    {
        $response = [];
        $parent = ChildrenClassificationMembers::find($this->children_classification_id);

        if ($parent) {
            $response["id"] = $parent->id;
            $response["title"] = $parent->title;
        }

        return $this->attributes['detail_children_classification'] = $response;
    }

    public function getDetailCountryAttribute()
    {
        $response = [];
        $data = Country::find($this->country_id);

        if ($data) {
            $response["id"] = $data->id;
            $response["name"] = $data->name;
        }

        return $this->attributes['detail_country'] = $response;
    }

    public function getDetailProvincyAttribute()
    {
        $response = [];
        $data = ProvinceCountry::find($this->states_id);

        if ($data) {
            $response["id"] = $data->id;
            $response["name"] = $data->name;
        }

        return $this->attributes['detail_provincy'] = $response;
    }

    public function getDetailCityAttribute()
    {
        $response = [];
        $data = CityCountry::find($this->city_of_contry_id);

        if ($data) {
            $response["id"] = $data->id;
            $response["name"] = $data->name;
        }

        return $this->attributes['detail_city'] = $response;
    }

    public function getDetailClubAttribute()
    {
        $response = [];
        $data = ArcheryClub::find($this->archery_club_id);

        if ($data) {
            $response["id"] = $data->id;
            $response["name"] = $data->name;
        }

        return $this->attributes['detail_club'] = $response;
    }
}
