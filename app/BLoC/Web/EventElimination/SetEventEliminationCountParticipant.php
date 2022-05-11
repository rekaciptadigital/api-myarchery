<?php

namespace App\BLoC\Web\EventElimination;

use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Exceptions\BLoCException;

class SetEventEliminationCountParticipant extends Transactional
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

        $category->update([
            "default_elimination_count" => $parameters->get("count_elimination_participant")
        ]);

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            'event_category_id' => 'required|exists:archery_event_category_details,id',
            "count_elimination_participant" => "required|in:0,8,16,32,64,128"
        ];
    }
}
