<?php

namespace App\BLoC\App\Certificate;

use App\Models\ArcheryEventCertificateTemplates;
use App\Models\ArcheryMemberCertificate;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEvent;
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
    $user = Auth::guard('app-api')->user();

    $events = ArcheryEventParticipantMember::select("archery_event_participants.event_id")
      ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
      ->join("archery_events", "archery_event_participants.event_id", "=", "archery_events.id")
      ->where("archery_event_participants.type", "individual")
      ->where("archery_event_participant_members.user_id", $user["id"])
      ->where("archery_event_participants.status", 1)
      ->groupBy("archery_event_participants.event_id")
      ->orderBy("archery_events.event_end_datetime", "DESC")
      ->get();

    $output = [];
    foreach ($events as $key => $value) {
      $event = ArcheryEvent::find($value->event_id);
      $certificate = ArcheryMemberCertificate::prepareUserCertificate($event->id, $user["id"]);
      if (!empty($certificate)) {
        $output[] = [
          "event_id" => $event->id,
          "event_name" => $event->event_name,
          "certificates" => $certificate
        ];
      }
    }
    // $participant_id = $parameters->get('participant_id');
    // $user = Auth::guard('app-api')->user();
    // $detail_info = new \stdClass();

    // $member=ArcheryEventParticipant::getMemberByUserId($user['id'],$participant_id);
    // if(!$member)throw new BLoCException("anda tidak mengikuti event ini");

    // $event_id = $member->event_id;
    // $member_id = $member->id;

    // $detail_info->member_name=$member->name;
    // $detail_info->member_id=$member_id;

    // $category=ArcheryEventCertificateTemplates::getCategoryLabel($participant_id,$user['id']);
    // if($category == "")throw new BLoCException("kategoru tidak ditemukan");
    // $detail_info->category_name = $category;

    // $certificate=[];
    // $list = ArcheryEventCertificateTemplates::getTypeCertificate();
    // $participant_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['participant']);
    // if($participant_certificate){
    //   unset($participant_certificate->html_template);
    //   $certificate[]=["type" => "participant", "data" => (object)array_merge((array)$participant_certificate, (array)$detail_info)];
    // }
    // $get_peringkat=ArcheryEventCertificateTemplates::checkElimination($member_id);
    // if($get_peringkat){
    //   $ranked=$get_peringkat->elimination_ranked;
    //   $detail_info->ranked=$ranked;

    //     $elimination_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['elimination']);
    //     if($elimination_certificate){
    //         unset($elimination_certificate->html_template);
    //         $certificate[]=["type" => "elimination", "data" =>  (object)array_merge((array)$elimination_certificate, (array)$detail_info)];
    //     }
    //     if($ranked != 0){
    //       $ranking_certificate=ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id,$list['winner']);

    //       if($ranking_certificate){
    //           unset($ranking_certificate->html_template);
    //           $certificate[]=["type" => "winner", "data" => (object)array_merge((array)$ranking_certificate, (array)$detail_info)];
    //       }
    //     }
    // }

    return $output;
  }
}
