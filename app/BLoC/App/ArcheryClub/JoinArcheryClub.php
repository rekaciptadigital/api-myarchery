<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class JoinArcheryClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $club_id = $parameters->get('club_id');
        $club = ArcheryClub::find($club_id);
        if (!$club) {
            throw new BLoCException("club not found");
        }

        $user =  $user = Auth::guard('app-api')->user();

        $isExist = ClubMember::where('club_id', $club_id)
        ->where('user_id', $user->id)->get();

        if ($isExist->count() > 0) {
            throw new BLoCException("user already join this club");
        }

        $member = ClubMember::addNewMember($club_id, $user->id, 1, 2);

        return $member;
    }

    protected function validation($parameters)
    {
        return [
            'club_id' => 'required|integer'
        ];
    }
}
