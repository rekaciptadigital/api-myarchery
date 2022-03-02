<?php

namespace App\BLoC\App\Series;

use App\Models\ArcherySerie;
use DAI\Utils\Abstracts\Retrieval;

class GetListSeries extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $series = ArcherySerie::all();
        return $series;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
