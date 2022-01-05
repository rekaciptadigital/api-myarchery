<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetMyClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;

        $user =  $user = Auth::guard('app-api')->user();
        $club_member = ClubMember::where('user_id', $user->id);
        $club_member->limit($limit)->offset($offset);

        $data = [];
        foreach ($club_member->get() as $key) {
            $club = ArcheryClub::find($key->club_id);
            $total_member = ClubMember::where('club_id', $club->id)->get()->count();
            $club['total_member'] = $total_member;
            $club['is_admin'] =  $key->role == 1 ? 1 : 0;
            array_push($data, $club);
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
