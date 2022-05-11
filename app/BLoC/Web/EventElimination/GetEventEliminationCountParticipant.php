<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventElimination;
use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationMatch;
use DAI\Utils\Exceptions\BLoCException;

class GetEventEliminationCountParticipant extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_category_id = $parameters->get("event_category_id");

        $category = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$category) {
            throw new BLoCException("kategori tidak ada");
        }

        $respose = [];
        $elimination = ArcheryEventElimination::where("event_category_id", $event_category_id)->first();
        if ($elimination) {
            $respose = [
                "elimination_id" => $elimination->id,
                "count_participant" => $elimination->count_participant
            ];
        }

        return $respose;
    }

    protected function validation($parameters)
    {
        return [
            'event_category_id' => 'required|exists:archery_event_category_details,id',
        ];
    }
}
