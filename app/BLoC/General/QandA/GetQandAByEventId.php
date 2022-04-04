<?php

namespace App\BLoC\General\QandA;

use App\Models\ArcheryEvent;
use App\Models\QandA;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetQandAByEventId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;


        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        $list_q_and_a_query = QandA::where("event_id", $event_id);
        $list_q_and_a_collection = $list_q_and_a_query->orderBy("sort", "DESC")->limit($limit)->offset($offset)->get();

        return $list_q_and_a_collection;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "page" => "numeric|min:1",
            "limit" => "numeric|min:10"
        ];
    }
}
