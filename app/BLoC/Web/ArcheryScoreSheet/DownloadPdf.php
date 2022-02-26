<?php

namespace App\BLoC\Web\ArcheryScoreSheet;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ParticipantMemberTeam;
use App\Models\BudRest;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;


class DownloadPdf extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_id = $parameters->get('event_category_id');
        $download = BudRest::downloadQualificationScoreSheet($category_id,true);
        return env('APP_HOSTNAME') . $download["url"];
    }

    protected function validation($parameters)
    {
        return [
            "event_category_id" => 'required|integer'
        ];
    }
}
