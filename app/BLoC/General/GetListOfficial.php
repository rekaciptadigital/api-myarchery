<?php

namespace App\BLoC\General;

use App\Models\ArcheryEventOfficial;
use App\Models\City;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

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
