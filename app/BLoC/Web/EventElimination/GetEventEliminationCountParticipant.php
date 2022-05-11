<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventElimination;
use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
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

        $event_id = $category->event_id;

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        $elimination = ArcheryEventElimination::where("event_category_id", $event_category_id)->first();
        if (!$elimination) {
            return [];
        }

    }

    protected function validation($parameters)
    {
        return [
            'elimination_member_count' => 'required',
            'match_type' => 'required',
            'event_category_id' => 'required|exists:archery_event_category_details,id',
        ];
    }
}
