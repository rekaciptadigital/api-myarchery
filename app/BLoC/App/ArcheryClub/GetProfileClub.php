<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetProfileClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_club = ArcheryClub::find($parameters->get('id'));
        if (!$archery_club) {
            throw new BLoCException("club not found");
        }

        $club_member = ClubMember::where('club_id', $archery_club->id)->get();
        $data = [];
        $data['detail'] = $archery_club;

        if (Auth::guard('app-api')->user() != null) {
            $data['member'] = [];
            foreach ($club_member as $key) {
                $user = User::find($key->user_id);
                array_push($data['member'], $user);
            }
        }
        return $data;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
