<?php

namespace App\BLoC\Web\ArcheryEventCategories;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventCategoryDetail;

class GetArcheryEventCategoryRegister extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $data = ArcheryEventCategoryDetail::getCategoriesRegisterEvent($parameters->get('event_id'));
        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
        ];
    }
}