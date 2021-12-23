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
        $club_member = ClubMember::find($parameters->get('id'));
        if(!$club_member){
            return response()->json([
                'status' => 'error',
                'message' => 'data not found'
            ], 404);
        }

        $club_member->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'data deleted'
        ]);
    }

    protected function validation($parameters)
    {
        return [
            'user_id' => 'required|integer',
            'club_id' => 'required|integer'
        ];
    }
}
