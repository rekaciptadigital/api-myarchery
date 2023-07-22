<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;

class GetEventEliminationTemplate extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_category_id = $parameters->get("event_category_id");
        $event_id = $parameters->get("event_id");

        $event = ArcheryEvent::find($event_id);

        $category = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        if (strtolower($team_category->type) == "team") {
            return ArcheryEventParticipant::getTemplateTeam($category);
        }

        if (strtolower($team_category->type) == "individual") {
            return ArcheryEventParticipant::getTemplateIndividu($category, $event);
        }

        throw new BLoCException("gagal menampilkan template");
    }

    protected function validation($parameters)
    {
        return [];
    }
}
