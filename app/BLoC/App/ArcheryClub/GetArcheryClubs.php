<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetArcheryClubs extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_clubs = ArcheryClub::query();
        $user = Auth::guard('app-api')->user();
        $club = [];
        $data = [];



        $name = $parameters->get('name');
        $province = $parameters->get('province');
        $city = $parameters->get('city');

        $archery_clubs->when($name, function ($query) use ($name) {
            return $query->whereRaw("name LIKE '%" . $name . "%'");
        });

        $archery_clubs->when($province, function ($query) use ($province) {
            return $query->where("province", $province);
        });

        $archery_clubs->when($city, function ($query) use ($city) {
            return $query->where("city", $city);
        });

        foreach ($archery_clubs->get() as $key) {
            $club['detail'] = $key;
            $club['total'] = ClubMember::where('club_id', $key->id)->count();
            $club['status_keanggotaan'] = ClubMember::getStatus($key->id, $user->id);
            array_push($data, $club);
        }

        return  $data;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
