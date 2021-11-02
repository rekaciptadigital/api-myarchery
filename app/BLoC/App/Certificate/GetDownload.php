<?php

namespace App\BLoC\App\Certificate;

use App\Models\ArcheryEventCertificateTemplates;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Models\UserArcheryInfo;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Dompdf\Dompdf;
use Dompdf\Options;
//use Knp\Snappy\Pdf;
use Illuminate\Support\Facades\App;

class GetDownload extends Retrieval
{
  public function getDescription()
  {
    return "";
  }

  protected function process($parameters)
  {
    $event_id = $parameters->get('event_id');
    $user_id = $parameters->get('user_id');
    $type_certificate = $parameters->get('type_certificate');
    $download=ArcheryEventCertificateTemplates::getDownload($event_id, $type_certificate,$user_id);
    return $download;

  }


}
