<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventParticipant;

class GetArcheryEvent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        // $archery_event = ArcheryEvent::where('admin_id', $admin['id'])->orderBy('created_at', 'DESC')->get();
        $archery_event = ArcheryEvent::select("archery_events.*")->leftJoin("admin_roles", "admin_roles.event_id", "=", "archery_events.id")->where("archery_events.admin_id", $admin->id)
            ->orWhere("admin_roles.admin_id", $admin->id)
            ->orderBy('created_at', 'DESC')->distinct()
            ->get();

        $output = [];
        foreach ($archery_event as $key => $value) {
            $total_participant = ArcheryEventParticipant::where("event_id", $value->id)->where("status", 1)->count();
            $output[] = array(
                "event" => $value,
                "total_participant" => $total_participant
            );
        }
        return $output;
    }
}
