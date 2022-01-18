<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetListArcheryEventDetail extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $archery_event_detail = ArcheryEvent::detailEventById();
        return $archery_event_detail;
    }

    protected function validation($parameters)
    {
        return [
        ];
    }
}