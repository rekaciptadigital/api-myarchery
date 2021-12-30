<?php

namespace App\BLoC\App\ArcheryClub;

use App\Libraries\Upload;
use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateArcheryClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $data = $parameters->all();
        $archery_club = ArcheryClub::find($parameters->get('id'));

        if (!$archery_club) {
            throw new BLoCException("data not found");
        }

        $owner = ClubMember::where('club_id', $archery_club->id)->where('role', 1)->first();
        if (!$owner) {
            throw new BLoCException("user not owner");
        }

        $user_login = Auth::guard('app-api')->user();
        if ($owner->user_id != $user_login->id) {
            throw new BLoCException("forbiden 403");
        }

        if ($parameters->get('logo')) {
            $logo = Upload::setPath("asset/logo/")->setFileName("logo_" . $archery_club->id)->setBase64($parameters->get('logo'))->save();
            $data['logo'] = $logo;
        };

        if ($parameters->get('banner')) {
            $banner = Upload::setPath("asset/banner/")->setFileName("banner_" . $archery_club->id)->setBase64($parameters->get('logo'))->save();
            $data['banner'] = $banner;
        };

        unset($data['id']);
        $archery_club->fill($data);
        $archery_club->save();

        return $archery_club;
    }

    protected function validation($parameters)
    {
        return [
            'name' => [
                'string',
                Rule::unique('archery_clubs')->ignore($parameters->get('id'))
            ],
            'place_name' => 'string',
            'province' => "integer",
            'city' => 'integer',
            'logo' => 'string',
            'address' => 'string',
            'description' => 'string',
            'banner' => 'string'
        ];
    }
}
