<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetAllMemberByClubId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_club = ArcheryClub::find($parameters->get('club_id'));
        if (!$archery_club) {
            throw new BLoCException("club not found");
        }

        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;

        $club_member = ClubMember::where('club_id', $archery_club->id);
        $club_member->limit($limit)->offset($offset);
        $data = [];
        foreach ($club_member->get() as $key) {
            $user = User::find($key->user_id);
            array_push($data, $user);
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'page' => 'min:1',
            'limit' => 'min:1'
        ];
    }
}
