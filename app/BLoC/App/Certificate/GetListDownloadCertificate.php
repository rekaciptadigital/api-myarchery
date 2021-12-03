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
    $participant_id = $parameters->get('participant_id');
    $user = Auth::guard('app-api')->user();
    $detail_info = new \stdClass();

    $member=ArcheryEventParticipant::getMemberByUserId($user['id'],$participant_id);
    if(!$member)throw new BLoCException("anda tidak mengikuti event ini");
    
    $event_id = $member->event_id;
    $member_id = $member->id;

    $detail_info->member_name=$user['name'];
    $detail_info->member_id=$member_id;

    $category=ArcheryEventCertificateTemplates::getCategoryLabel($participant_id,$user['id']);
    if($category == "")throw new BLoCException("kategoru tidak ditemukan");
    $detail_info->category_name = $category;

    $certificate=[];
    $list = ArcheryEventCertificateTemplates::getTypeCertificate();
    $participant_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['participant']);
    if($participant_certificate){
      unset($participant_certificate->html_template);
      $certificate[]=["type" => "participant", "data" => (object)array_merge((array)$participant_certificate, (array)$detail_info)];
    }
    $get_peringkat=ArcheryEventCertificateTemplates::checkElimination($member_id);
    if($get_peringkat){
        $detail_info->peringkat_name=$get_peringkat->position_qualification;
        $ranked=$get_peringkat->elimination_ranked;

        $elimination_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['elimination']);
        if($elimination_certificate){
            unset($elimination_certificate->html_template);
            $certificate[]=["type" => "elimination", "data" =>  (object)array_merge((array)$elimination_certificate, (array)$detail_info)];
        }
        if($ranked != 0){
          $ranking_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['winner']);

          if($ranking_certificate){
              unset($ranking_certificate->html_template);
              $certificate[]=["type" => "winner", "data" => (object)array_merge((array)$ranking_certificate, (array)$detail_info)];
          }
        }
    }

    return $certificate;
  }

}
