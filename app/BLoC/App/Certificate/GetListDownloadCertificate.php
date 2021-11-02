<?php

namespace App\BLoC\App\Certificate;

use App\Models\ArcheryEventCertificateTemplates;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Helpers\BLoC;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\App;

class GetListDownloadCertificate extends Retrieval
{
  public function getDescription()
  {
    return "";
  }

  protected function process($parameters)
  {
    $event_id = $parameters->get('event_id');
    $user_id = $parameters->get('user_id');
    $checkUser=ArcheryEventCertificateTemplates::getParticipantByUserAndEvent($event_id,$user_id);
    if($checkUser="true"){
      $type_certificate="1";//type=participant
      $getCertificate=[];
      $getCertificate[]=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$type_certificate);//get participant certificate
      $getCertificate[]=ArcheryEventCertificateTemplates::checkEliminationByUserAndEvent($event_id,$user_id);//get elimination certificate

    return $getCertificate;

  }
}

  }
