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
        $archery_event = ArcheryEvent::where('admin_id', $admin['id'])->orderBy('created_at', 'DESC')->get();

        $output = [];
        foreach ($archery_event as $key => $value) {
            $total_participant = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                                ->where("event_id",$value->id)->where("transaction_logs.status",1)->count();
            $output[] = array(
                            "event" => $value,
                            "total_participant" => $total_participant
                        );
        }
        return $output;
    }
}
