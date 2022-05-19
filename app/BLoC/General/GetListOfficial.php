<?php

namespace App\BLoC\General;

use App\Models\ArcheryEventOfficial;
use DAI\Utils\Abstracts\Retrieval;

class GetListOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_official = new ArcheryEventOfficial;
        return $archery_official->getListOfficial();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
