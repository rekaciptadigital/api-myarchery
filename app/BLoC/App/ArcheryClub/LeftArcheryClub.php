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
        $club_member = ClubMember::find($parameters->get('id'));
        if(!$club_member){
            throw new BLoCException("data not found");
        }

        $club_member->delete();
    }

    protected function validation($parameters)
    {
        return [
        
        ];
    }
}
