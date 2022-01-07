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
        $archery_club = ArcheryClub::find($parameters->get('club_id'));
        if (!$archery_club) {
            throw new BLoCException("club not found");
        }


        $is_join = 0;
        $club_member = ClubMember::where('club_id', $archery_club->id)->get();
        if (Auth::guard('app-api')->user() != null) {
            $user = Auth::guard('app-api')->user();
            $is_join = $club_member->where('user_id', $user->id)->first() != null ? 1 : 0;
        }
        $archery_club['total_member'] = $club_member->count();
        $archery_club['is_join'] = $is_join ? 1 : 0;

        return $archery_club;
    }

    protected function validation($parameters)
    {
        return [
            'club_id' => 'required|integer'
        ];
    }
}
