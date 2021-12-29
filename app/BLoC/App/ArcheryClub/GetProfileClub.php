<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetprofileClub extends Retrieval
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
        foreach ($club_member as $key) {
            $user = User::find($key->user_id);
            array_push($data, $user);
        }
        return $data;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
