<?php

namespace App\BLoC\Web\ArcheryEventCertificateTemplates;

use App\Models\ArcheryEventCertificateTemplates;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;

class GetArcheryEventCertificateTemplates extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
      $event_id=$parameters->get("event_id");
      $type_certificate=$parameters->get("type_certificate");
      $query= ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$type_certificate);

      return $query;

    }
    protected function validation($parameters)
    {
      return [
          'event_id' => 'required',
          'type_certificate' => 'required',
      ];
    }
}
