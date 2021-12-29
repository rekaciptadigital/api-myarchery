<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class KickMember extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        return $parameters->get('id');
        $user_login = Auth::guard('app-api')->user();
        $owner = ClubMember::where('user_id', $user_login->id)->first();
        if(!$owner){
            throw new BLoCException("owner not found");
        }

        $club_member = ClubMember::find($parameters->get('id'));

        if (!$club_member) {
            throw new BLoCException("member not found");
        }

        if ($owner->role != 1) {
            throw new BLoCException("you are not owner this club");
        }

        if ($club_member->id == $owner->id) {
            throw new BLoCException("cannot kick you are owner this club");
        }

        if ($owner->club_id != $club_member->club_id) {
            throw new BLoCException("this user not member your club");
        }

        $club_member->delete();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
