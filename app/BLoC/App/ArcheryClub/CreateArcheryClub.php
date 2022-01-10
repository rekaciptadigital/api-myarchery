<?php

namespace App\BLoC\App\ArcheryClub;

use App\Libraries\Upload;
use App\Models\ArcheryClub;
use App\Models\City;
use App\Models\ClubMember;
use App\Models\Provinces;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class CreateArcheryClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $archery_club = new ArcheryClub();
        $archery_club->name = $parameters->get('name');
        $archery_club->place_name = $parameters->get('place_name');

        if ($parameters->get('province')) {
            $province = Provinces::find($parameters->get('province'));
            if (!$province) {
                throw new BLoCException("province not found");
            }
        }
        $archery_club->province = $parameters->get('province');

        if ($parameters->get('city')) {
            $city = City::where('province_id', $parameters->get('province'))->where('id', $parameters->get('city'))->first();
            if(!$city){
                throw new BLoCException("this city not match with the province");
            }
        }
        $archery_club->city = $parameters->get('city');
        
        $archery_club->address = $parameters->get('address');
        $archery_club->description = $parameters->get('description');
        $archery_club->save();
        if ($parameters->get('logo')) {
            $logo = Upload::setPath("asset/logo/")->setFileName("logo_" . $archery_club->id)->setBase64($parameters->get('logo'))->save();
            $archery_club->logo = $logo;
            $archery_club->save();
        };

        if ($parameters->get('banner')) {
            $banner = Upload::setPath("asset/banner/")->setFileName("banner_" . $archery_club->id)->setBase64($parameters->get('banner'))->save();
            $archery_club->banner = $banner;
            $archery_club->save();
        };

        ClubMember::addNewMember($archery_club->id, $user->id, 1, 1);

        return $archery_club;
    }

    protected function validation($parameters)
    {
        return [
            'name' => 'required|string|unique:archery_clubs',
            'place_name' => 'required|string',
            'province' => "required|integer",
            'city' => 'required|integer',
            'logo' => 'string',
            'banner' => 'string',
            'address' => 'required|string',
            'description' => 'string'
        ];
    }
}
