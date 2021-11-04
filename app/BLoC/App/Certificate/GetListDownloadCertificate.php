<?php

namespace App\BLoC\App\Certificate;

use App\Models\ArcheryEventCertificateTemplates;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;
use \stdClass;

class GetListDownloadCertificate extends Retrieval
{
  public function getDescription()
  {
    return "";
  }

  protected function process($parameters)
  {
    $event_id = $parameters->get('event_id');
    $member_id = $parameters->get('member_id');
    $user = Auth::guard('app-api')->user();
    $detail_info = new \stdClass();

    $checkUser=ArcheryEventParticipant::isParticipate($user['id'],$event_id);
    if(!$checkUser)throw new BLoCException("anda tidak mengikuti event ini");
    $detail_info->member_name=$checkUser->name;
    $detail_info->member_id=$member_id;

    $kategori=ArcheryEventCertificateTemplates::getCategoryLabel($event_id,$user['id']);
    if(!$kategori)throw new BLoCException("kategori tidak ditemukan");
    $detail_info->kategori_name=$kategori->label_team_categories." - ".$kategori->label_age_categories." - ".$kategori->label_competition_categories." - ".$kategori->label_distance."m";

    $certificate=[];
    $list = ArcheryEventCertificateTemplates::getTypeCertificate();
    $participant_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['partisipan']);
    if(!$participant_certificate)throw new BLoCException("event dan/ atau tipe sertifikat tidak ditemukan");

    unset($participant_certificate->html_template);
    $certificate[]=["type" => "participant", "data" => (object)array_merge((array)$participant_certificate, (array)$detail_info)];

    $get_peringkat=ArcheryEventCertificateTemplates::checkElimination($member_id);
    if(!$get_peringkat)throw new BLoCException("data eliminasi tidak ditemukan");
    $detail_info->peringkat_name=$get_peringkat->position_qualification;

    $check_elimination=ArcheryEventCertificateTemplates::checkElimination($member_id);
    if(!$check_elimination)throw new BLoCException("member_id tidak ditemukan");

    $elimination=$check_elimination->position_qualification;

    if($elimination!=0 && $elimination>3){
      $elimination_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['eliminasi']);
      if(!$elimination_certificate)throw new BLoCException("event dan/ atau tipe sertifikat tidak ditemukan");
      unset($elimination_certificate->html_template);
      $certificate[]=["type" => "eliminasi", "data" =>  (object)array_merge((array)$elimination_certificate, (array)$detail_info)];
    }else if($elimination!=0 && $elimination<3){
      $ranking_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['juara']);
      unset($ranking_certificate->html_template);
      if(!$ranking_certificate)throw new BLoCException("event dan/ atau tipe sertifikat tidak ditemukan");
      $certificate[]=["type" => "juara", "data" => (object)array_merge((array)$ranking_certificate, (array)$detail_info)];
    }

    return $certificate;
  }

}
