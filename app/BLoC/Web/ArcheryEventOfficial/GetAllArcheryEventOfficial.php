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
        $status = $parameters->get("status");
        $event_id = $parameters->get("event_id");

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        //hitung jumlah tersedia disini
        $archery_event_official_detail =  ArcheryEventOfficialDetail::where('event_id', $event_id)->first();
        if (!$archery_event_official_detail) {
            throw new BLoCException("official tidak di set di event ini");
        }

        $quota = $archery_event_official_detail->quota;

        $official_count = ArcheryEventOfficial::countEventOfficialBooking($archery_event_official_detail->id);

        $official_member_query = ArcheryEventOfficial::select('users.name as user_name', 'archery_clubs.name as club_name', 'users.email as email', 'users.phone_number as phone_number', 'archery_event_official.status as status_official', "transaction_logs.status as status_order", "transaction_logs.expired_time")
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_official.club_id')
            ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_official.transaction_log_id")
            ->join('users', 'users.id', '=', 'archery_event_official.user_id')
            ->join('archery_event_official_detail', 'archery_event_official_detail.id', '=', 'archery_event_official.event_official_detail_id')
            ->where('archery_event_official_detail.event_id', $event_id);

        // search by name
        $official_member_query->when($name, function ($query) use ($name) {
            return $query->whereRaw("users.name LIKE ?", ["%" . $name . "%"]);
        });

        // filter by status
        $official_member_query->when($status, function ($query) use ($status) {
            if ($status == 4) {
                return $query->where("archery_event_official.status", 4)->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", ">", time());
            } elseif ($status == 2) {
                return $query->where("archery_event_official.status", 2)->orWhere(function ($q) {
                    return $q->where("archery_event_official.status", 4)->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", "<", time());
                });
            } else {
                return $query->where("archery_event_official.status", $status);
            }
        });

        $official_member_collection = $official_member_query->get();

        if ($official_member_collection->count() > 0) {
            $sort_number = 1;
            $status_label = "none";
            foreach ($official_member_collection as $key => $value) {
                $value->sort_number = $sort_number;
                if ($value->status_official == 1) {
                    $status_label = "Lunas";
                } elseif ($value->status_official == 2) {
                    $status_label = "Expired";
                } elseif ($value->status_official == 3) {
                    $status_label = "Failed";
                } elseif ($value->status_official == 4) {
                    if ($value->status_order == 4 && $value->expired_time > time()) {
                        $status_label = "Pending";
                    }

                    if ($value->status_order == 4 && $value->expired_time < time()) {
                        $status_label = "Expired";
                    }
                } elseif ($value->status_order == null) {
                    $status_label = "Free";
                }

                $value->status_label = $status_label;
                $sort_number++;
            }
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
            'status' => "in:1,2,3,4"

        ];
    }
}
