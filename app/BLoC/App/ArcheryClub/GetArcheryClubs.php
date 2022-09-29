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
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;
        $archery_clubs = ArcheryClub::query();

        if (Auth::guard('app-api')->user() != null) {
            $user = Auth::guard('app-api')->user();
        }

        $club = [];
        $data = [];

        $name = $parameters->get('name');
        $province = $parameters->get('province');
        $city = $parameters->get('city');

        $archery_clubs->when($name, function ($query) use ($name) {
            return $query->whereRaw("name LIKE ?", ["%" . $name . "%"]);
        });

        $archery_clubs->when($province, function ($query) use ($province) {
            return $query->where("province", $province);
        });

        $archery_clubs->when($city, function ($query) use ($city) {
            return $query->where("city", $city);
        });

        $archery_clubs->limit($limit)->offset($offset);

        foreach ($archery_clubs->get() as $key) {
            $club['detail'] = $key;
            $club['total_member'] = ClubMember::where('club_id', $key->id)->count();
            $club['is_join'] = !empty($user) ? ClubMember::getStatus($key->id, $user->id) : 0;
            array_push($data, $club);
        }

        return  $data;
    }

    protected function validation($parameters)
    {
        return [
            'page' => 'min:1',
            'limit' => 'min:1'
        ];
    }
}
