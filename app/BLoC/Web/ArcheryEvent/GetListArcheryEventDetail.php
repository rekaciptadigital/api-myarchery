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
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;
        $event_name = $parameters->get('event_name');

        $archery_event_detail = ArcheryEvent::detailEventAll($limit,$offset,$event_name);
        return $archery_event_detail;
    }

    protected function validation($parameters)
    {
        return [
            'limit' => 'required|integer',
            'page' => 'required|integer'
        ];
    }
}