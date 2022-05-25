<?php

namespace App\BLoC\Web\ArcheryEventCertificateTemplates;

use App\Models\ArcheryEventCertificateTemplates;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;

class GetArcheryEventCertificateTemplates extends Retrieval
{
  public function getDescription()
  {
    return "";
  }

  protected function process($parameters)
  {
    $admin = Auth::user();
    $event_id = $parameters->get('event_id');
    $checkAdmin = ArcheryEvent::isOwnEvent($admin['id'], $event_id);
    if (!$checkAdmin) throw new BLoCException("event tidak ditemukan");

    $type_certificate = $parameters->get("type_certificate");
    $query = ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id, $type_certificate);
    //dd($query);

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
