<?php

namespace App\BLoC\App\ArcheryEventOfficial;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficial;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AddOrderOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();
        $relation_id = $parameters->get('relation_id');
        $club_id = $parameters->get('club_id');
        $event_id = $parameters->get('event_id');

        if ($relation_id == 0) {
            Validator::make($parameters->all(), [
                "other" => "required|string",
            ])->validate();
        }

        if ($club_id != 0) {
            $club = ArcheryClub::find($club_id);
            if (!$club) {
                throw new BLoCException("club tidak ditemukan");
            }
        }

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        ArcheryEventOfficial::create([
            'user_id' => $user_login->id,
            'club_id' => $club_id,
            
        ]);
    }

    protected function validation($parameters)
    {
        return [
            'relation_id' => 'required|integer|in:1,2,3,4,0',
        ];
    }
}
