<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
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

        $member = ClubMember::where('club_id', $archery_club->id)->get()->toArray();

        $data = $archery_club;
        $data['total_member'] = count($member);

        return $data;
    }

    protected function validation($parameters)
    {
       return [

       ];
    }
}
