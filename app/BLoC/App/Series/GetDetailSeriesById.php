<?php

namespace App\BLoC\App\Series;

use App\Models\ArcherySerie;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetDetailSeriesById extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $series_id = $parameters->get("series_id");
        $series = ArcherySerie::find($series_id);
        if (!$series) {
            throw new BLoCException("series tidak ada");
        }

        return $series;
    }

    protected function validation($parameters)
    {
        return [
            "series_id" => "required|integer"
        ];
    }
}
