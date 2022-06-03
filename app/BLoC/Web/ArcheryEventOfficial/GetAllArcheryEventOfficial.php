<?php

namespace App\BLoC\Web\ArcheryEventOfficial;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficialDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventOfficial;

class GetAllArcheryEventOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $name = $parameters->get('name');

        $event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$event) {
            throw new BLoCException("event not found");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        //hitung jumlah tersedia disini
        $archery_event_official_detail =  ArcheryEventOfficialDetail::where('event_id', $parameters->get('event_id'))->first();
        if (!$archery_event_official_detail) {
            throw new BLoCException("official tidak di set di event ini");
        }

        $quota = $archery_event_official_detail->quota;

        $official_count = ArcheryEventOfficial::countEventOfficialBooking($archery_event_official_detail->id);

        $official_member_query = ArcheryEventOfficial::select('users.name as user_name', 'archery_clubs.name as club_name', 'users.email as email', 'users.phone_number as phone_number')
            ->where('archery_event_official_detail.event_id', $parameters->get('event_id'))
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_official.club_id')
            ->leftJoin('users', 'users.id', '=', 'archery_event_official.user_id')
            ->leftJoin('archery_event_official_detail', 'archery_event_official_detail.id', '=', 'archery_event_official.event_official_detail_id')
            ->where('archery_event_official.status', 1);

        // search by name
        $official_member_query->when($name, function ($query) use ($name) {
            return $query->whereRaw("users.name LIKE ?", ["%" . $name . "%"]);
        });

        $official_member_collection = $official_member_query->get();

        if ($official_member_collection->isEmpty()) {
            throw new BLoCException("data not found");
        }

        $sort_number = 1;
        foreach ($official_member_collection as $key => $value) {
            $value->sort_number = $sort_number;
            $sort_number++;
        }

        $data = [
            "kuota_event" => $quota,
            "sisa_kuota" => $quota - $official_count,
            "member" => $official_member_collection
        ];

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => [
                'required'
            ],

        ];
    }
}
