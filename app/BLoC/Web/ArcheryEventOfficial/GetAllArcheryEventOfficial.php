<?php

namespace App\BLoC\Web\ArcheryEventOfficial;

use App\Models\User;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficialDetail;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Mpdf\Output\Destination;
use Illuminate\Support\Facades\DB;
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

        //hitung jumlah tersedia disini
        $archery_event_official_detail =  ArcheryEventOfficialDetail::where('event_id', $parameters->get('event_id'))->first();

        if ($archery_event_official_detail) {

            if ($archery_event_official_detail->individual_quota != 0) {
                $quota_total = $archery_event_official_detail->individual_quota;
            } else {
                $quota = $archery_event_official_detail->club_quota;
                $count = ArcheryEventOfficial::count(DB::raw('DISTINCT club_id'));
                $quota_total = $quota * $count;
            }
        }

        $official_count = ArcheryEventOfficial::countEventOfficialBooking($archery_event_official_detail->id);

        $official_member = ArcheryEventOfficial::select('users.name as user_name', 'archery_clubs.name as club_name', 'users.email as email', 'users.phone_number as phone_number')
            ->where('archery_event_official_detail.event_id', $parameters->get('event_id'))
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_official.club_id')
            ->leftJoin('users', 'users.id', '=', 'archery_event_official.user_id')
            ->leftJoin('archery_event_official_detail', 'archery_event_official_detail.id', '=', 'archery_event_official.event_official_detail_id')
            ->where(function ($query) use ($name) {
                if (!empty($name)) {
                    $query->whereRaw("users.name LIKE ?", ["%" . $name . "%"]);
                }
            })
            ->get();

        if ($official_member->isEmpty()) {
            throw new BLoCException("data not found");
        }

        $data = [
            "kuota_event" => $quota_total,
            "sisa_kuota" => $quota_total - $official_count,
            "member" => $official_member
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
