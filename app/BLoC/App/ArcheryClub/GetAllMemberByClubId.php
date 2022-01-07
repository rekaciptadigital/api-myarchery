<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;

class GetAllMemberByClubId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_club = ArcheryClub::find($parameters->get('club_id'));
        $name = $parameters->get('name');
        $gender = $parameters->get('gender');
        if (!$archery_club) {
            throw new BLoCException("club not found");
        }

        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;

        $club_member = ClubMember::where('club_id', $archery_club->id)->join('users', 'users.id', '=', 'archery_club_members.user_id');

        $club_member->when($name, function ($query) use ($name) {
            return $query->whereRaw("name LIKE ?", ["%" . $name . "%"]);
        });

        $club_member->when($gender, function ($query) use ($gender) {
            return $query->where('gender', $gender);
        });

        $club_member->limit($limit)->offset($offset);
        $data = [];
        foreach ($club_member->get() as $key) {
            $user = User::find($key->user_id);
            $user['member_id'] = $key->id;
            if($key->role == 1){
                $user['is_admin'] = 1;
            }else{
                $user['is_admin'] = 0;
            }
            array_push($data, $user);
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'club_id' => 'required|integer',
            'page' => 'min:1',
            'limit' => 'min:1'
        ];
    }
}
