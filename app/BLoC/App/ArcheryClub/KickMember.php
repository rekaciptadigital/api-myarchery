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
        $user_login = Auth::guard('app-api')->user();
        $club_id = $parameters->get('club_id');
        $member_id =  $parameters->get('member_id');

        $club = ArcheryClub::find($club_id);
        if(!$club){
            throw new BLoCException("club not found");
        }

        $member = ClubMember::where('user_id', $member_id)->where('club_id', $club_id)->first();
        if(!$member){
            throw new BLoCException("member not found");
        }


        $owner = ClubMember::where('user_id', $user_login->id)->where('club_id', $club_id)->where('role', 1)->first();
        if(!$owner){
            throw new BLoCException("you are not owner this club");
        }


        if($member->role == 1){
            throw new BLoCException("this user owner this club");
        }

        if($member->club_id != $club->id){
            throw new BLoCException("this user not member this club");
        }

        $member->delete();
    }

    protected function validation($parameters)
    {
        return [
            'club_id' => 'required|integer',
            'member_id' => 'required|integer'
        ];
    }
}
