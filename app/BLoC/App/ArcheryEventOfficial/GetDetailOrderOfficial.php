<?php

namespace App\BLoC\App\ArcheryEventOfficial;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;


class GetDetailOrderOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_official_id = $parameters->get('event_official_id');
        $event_official = ArcheryEventOfficial::find($event_official_id);
        if (!$event_official) {
            throw new BLoCException("data pesanan tidak ditemukan");
        }

        $detail_event_official = [];
        if ($event_official) {
            $detail_event_official = [
                'event_official_id' => $event_official->id,
                'type' => $event_official->type,
                'relation_with_participant' => $event_official->relation_with_participant,
                'relation_with_participant_label' => $event_official->relation_with_participant_label,
                'status' => $event_official->status,
                'status_label' => $event_official->getStatusLabel($event_official->status)
            ];
        }

        $detail_event_official_detail = [];
        $event_official_detail = ArcheryEventOfficialDetail::find($event_official->event_official_detail_id);
        if ($event_official_detail) {
            $detail_event_official_detail = [
                'event_official_detail_id' => $event_official_detail->id,
                'quota' => $event_official_detail->quota,
                'fee' => $event_official_detail->fee,
            ];
        }

        $detail_user = [];
        $user = User::find($event_official->user_id);
        if ($user) {
            $detail_user = [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar' => $user->avatar,
                'date_of_birth' => $user->date_of_birth,
                'age' => $user->age,
                'gender' => $user->gender,
            ];
        }

        $club = ArcheryClub::find($event_official->club_id);

        $detail_event = [];
        $event = ArcheryEvent::find($event_official_detail->event_id);
        if($event){
            $detail_event = $event->getDetailEventById($event->id);
        }

        $output = [
            'detail_event_official' => $detail_event_official,
            'detail_event_official_detail' => $detail_event_official_detail,
            'detail_user' => $detail_user,
            'club_detail' => $club,
            'detail_event' => $detail_event
        ];

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'event_official_id' => 'required|integer',
        ];
    }
}
