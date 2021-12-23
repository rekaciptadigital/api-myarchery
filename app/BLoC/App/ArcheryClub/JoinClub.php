<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class JoinClub extends Retrieval
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
            return response()->json([
                "status" => "error",
                "message" => "club not found"
            ], 404);
        }

        $user_id = Auth::user()->id;

        $isExist = ClubMember::where('club_id', $club_id)
        ->where('user_id', $user_id)->get();

    if ($isExist->count() > 0) {
        return response()->json([
            "status" => "error",
            "message" => "user already join this club"
        ], 409);
    }

        $member = new ClubMember();
        $member->user_id = $user_id;
        $member->club_id = $club_id;
        $member->save();

        return $member;
    }

    protected function validation($parameters)
    {
        return [
            'user_id' => 'required|integer',
            'club_id' => 'required|integer'
        ];
    }
}
