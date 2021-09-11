<?php

namespace App\BLoC\Web\ArcheryEventScoringSystem;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class AddArcheryEventScoringSystem extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $archery_event = ArcheryEvent::where('admin_id', $admin['id'])->orderBy('created_at', 'DESC')->get();

        return $archery_event;
    }
}
