<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetMyClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // $archery_clubs = ArcheryClub::query();
        // $name = $parameters->get('name');

        // $archery_clubs->when($name, function ($query) use ($name) {
        //     return $query->whereRaw("name LIKE '%" . strtolower($name) . "%'");
        // });

        // $data = $archery_clubs->limit(25)->get();
        // return $data;

        $user =  $user = Auth::guard('app-api')->user();
        $club_member = ClubMember::where('user_id', $user->id)->get();
        return $club_member;
    }

    protected function validation($parameters)
    {
       return [

       ];
    }
}
