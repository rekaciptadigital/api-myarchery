<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use App\Models\User;
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
        $user =  $user = Auth::guard('app-api')->user();
        $club_member = ClubMember::where('user_id', $user->id);
        $data = [];
        foreach($club_member->get() as $key){
            $club = ArcheryClub::find($key->club_id);
            array_push($data, $club);
        }
        return $data;
    }

    protected function validation($parameters)
    {
       return [

       ];
    }
}
