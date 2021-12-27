<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class LeftArcheryClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();
        $club_member = ClubMember::where('user_id', $user_login->id)->first();
        
        if (!$club_member) {
            throw new BLoCException("user not found");
        }

        if ($club_member->role == 1) {
            throw new BLoCException("user as a owner this club");
        }

        $club_member->delete();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
