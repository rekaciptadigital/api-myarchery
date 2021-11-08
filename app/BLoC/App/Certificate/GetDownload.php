<?php

namespace App\BLoC\App\Certificate;

use App\Models\ArcheryEventCertificateTemplates;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;

class GetDownload extends Retrieval
{
  public function getDescription()
  {
    return "";
  }

  protected function process($parameters)
  {
    $event_id = $parameters->get('event_id');
    $user = Auth::guard('app-api')->user();
    $member_id = $parameters->get('member_id');

    $checkUser=ArcheryEventParticipant::isParticipate($user['id'],$event_id);
    if(!$checkUser)throw new BLoCException("anda tidak mengikuti event ini");
    $member_name=$checkUser->name;

    $type_certificate = $parameters->get('type_certificate');
    $certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$type_certificate);
    if(!$certificate)throw new BLoCException("event dan/atau tipe sertifikat tidak ditemukan");

    $html_template=base64_decode($certificate->html_template);

    $kategori=ArcheryEventCertificateTemplates::getCategoryLabel($event_id,$user['id']);
    if(!$kategori)throw new BLoCException("kategori tidak ditemukan");

    $kategori_name=$kategori->label_team_categories." - ".$kategori->label_age_categories." - ".$kategori->label_competition_categories." - ".$kategori->label_distance."m";

    $list = ArcheryEventCertificateTemplates::getTypeCertificate();

    if($type_certificate==$list['juara']){
      $get_peringkat=ArcheryEventCertificateTemplates::checkElimination($member_id);
      if(!$get_peringkat)throw new BLoCException("data eliminasi tidak ditemukan");
      $peringkat_name=$get_peringkat->position_qualification;

      $final_doc=$template=str_replace(['{%member_name%}', '{%kategori_name%}','{%peringkat_name%}'], [$member_name, $kategori_name,$peringkat_name],$html_template);
    }else{
      $final_doc=$template=str_replace(['{%member_name%}', '{%kategori_name%}'], [$member_name, $kategori_name],$html_template);
    }

    $mpdf = new \Mpdf\Mpdf([
      'margin_left' => 0,
      'margin_right' => 0,
      'mode' => 'utf-8',
      'format' => 'A4-L',
      'orientation' => 'L',
      'bleedMargin' => 0,
      'dpi'        => 110,
    ]);

    $mpdf->SetDisplayPreferences('FullScreen');
    $mpdf->WriteHTML($final_doc);
    $mpdf->Output();
  }

}
