<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetArcheryEventDetailById extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $archery_event = ArcheryEvent::find($parameters->get('id'));
        if (!$archery_event) {
            throw new BLoCException("Data not found");
        }

        if ($archery_event->admin_id != $admin->id) {
            $roles = AdminRole::where("admin_id", $admin->id)->where("event_id", $archery_event->id)->where(function ($q) {
                $q->where("role_id", 5)->orWhere("role_id", 4)->orWhere("role_id", 6);
            })->first();
            if (!$roles) {
                throw new BLoCException("forbiden");
            }
        }

        $archery_event_detail = ArcheryEvent::detailEventById($parameters->get('id'));
        return $archery_event_detail;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer',
        ];
    }
}
