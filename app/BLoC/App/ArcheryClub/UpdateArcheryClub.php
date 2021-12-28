<?php

namespace App\BLoC\App\ArcheryClub;

use App\Libraries\Upload;
use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateArcheryClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();

        $archery_club = ArcheryClub::find($parameters->get('id'));
        if (!$archery_club) {
            throw new BLoCException("data not found");
        }
    
        $archery_club->name = $parameters->get('name');
        $archery_club->place_name = $parameters->get('place_name');
        $archery_club->province = $parameters->get('province');
        $archery_club->city = $parameters->get('city');
        $archery_club->address = $parameters->get('address');
        $archery_club->description = $parameters->get('description');
        $archery_club->save();
        if ($parameters->get('logo')) {
            $file = Upload::setPath("asset/logo/")->setFileName("logo_".$archery_club->id)->setBase64($parameters->get('logo'))->save();
            $archery_club->logo = $file;
            $archery_club->save();
        };

        return $archery_club;
    }

    protected function validation($parameters)
    {
        return [
            'name' => 'string|unique:archery_clubs',
            'place_name' => 'string',
            'province' => "string",
            'city' => 'string',
            'logo' => 'string',
            'address' => 'string',
            'description' => 'string'
        ];
    }
}