<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Models\UserArcheryInfo;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Dompdf\Dompdf;
use Dompdf\Options;

class ArcheryEventCertificateTemplates extends Model
{
  protected $fillable = [
    'event_id',
    'html_template',
    'background_url',
    'editor_data',
    'type_certificate',
  ];
  
  public static function  getCertificateByEventAndType($event_id, $type_certificate)
  {
    $archery_event_certificate_templates =DB::table('archery_event_certificate_templates')->where('event_id', $event_id)->where('type_certificate', $type_certificate)->first();
    if(!$archery_event_certificate_templates){
      return false;
    }else{
      return $archery_event_certificate_templates;
    }
  }
  public static function  getParticipantByUserAndEvent($event_id, $user_id)
  {
    $archery_event_participants =DB::table('archery_event_participants')->where('event_id', $event_id)->where('user_id', $user_id)->first();
    if(!$archery_event_participants){
      return "false";
    }else{
      return "true";
    }
  }
  public static function  getUserDetail($event_id,$user_id)
  {
    $archery_event_participants =DB::select('select p.user_id as user_id, p.event_id as event_id,m.id as member_id,
    em.position_qualification as peringkat, p.name as name
    from archery_event_participants p
    left join archery_event_participant_members m on m.archery_event_participant_id=p.id
    left join archery_event_elimination_members em on em.member_id=m.id
    where p.user_id='.$user_id.' and p.event_id='.$event_id);

    return $archery_event_participants;
  }
  public static function  checkEliminationByUserAndEvent($event_id,$user_id)
  {
    $archery_event_participants =DB::select('select p.user_id as user_id, p.event_id as event_id,m.id as member_id,
    em.position_qualification as position_qualification
    from archery_event_participants p
    left join archery_event_participant_members m on m.archery_event_participant_id=p.id
    left join archery_event_elimination_members em on em.member_id=m.id
    where p.user_id='.$user_id.' and p.event_id='.$event_id);

    if(count($archery_event_participants)!=0){
      foreach ($archery_event_participants as $elimination) {
        if( $elimination->position_qualification!=0){
          $type_certificate=2;
          $response=self::getCertificateByEventAndType($event_id, $type_certificate);
          return $response;
        }else{
          $type_certificate=3;
          $response=self::getCertificateByEventAndType($event_id, $type_certificate);
          return $response;
        }


      }
    }
  }
  public static function  getCategoryLabel($event_id,$user_id)
  {
    $label =DB::select('select CONCAT(tc.label," - ", ac.label," - ", cc.label," - ", d.label,"m") AS label
    from archery_event_participants p
    left join archery_master_team_categories tc ON tc.id=p.team_category_id
    left join archery_master_age_categories ac on ac.id=p.age_category_id
    left join archery_master_competition_categories cc on cc.id=p.competition_category_id
    left join archery_master_distances d on d.id=p.distance_id
    where p.user_id='.$user_id.' and p.event_id='.$event_id);

    foreach($label as $kategori){
      $kategori_nama=$kategori->label;
    }
    return $kategori_nama;
  }

  public static function  getDownload($event_id, $type_certificate,$user_id)
  {
    $certificate=self::getCertificateByEventAndType($event_id,$type_certificate);
    $data=$certificate["details"];

    $event_id=$data['event_id'];
    $html_template=base64_decode($data['html_template']);
    $background_url=$data['background_url'];
    $editor_data=$data['editor_data'];



    $kategori_name=self::getCategoryLabel($event_id,$user_id);
    $peringkatDanNama=self::getUserDetail($event_id,$user_id);
    foreach($peringkatDanNama as $details){
      $nama=$details->name;
      $peringkat=$details->peringkat;
    }


    if($type_certificate=2){
      $final_doc=$template=str_replace(['{%member_name%}', '{%kategori_name%}','{%peringkat_name%}'], [$nama, $kategori_name,$peringkat],$html_template);
    }else{
      $final_doc=$template=str_replace(['{%member_name%}', '{%kategori_name%}'], [$nama, $kategori_name],$html_template);
    }

    $mpdf = new \Mpdf\Mpdf([
    'margin_left' => 0,
    'margin_right' => 0,
    'mode' => 'utf-8',
    'format' => 'A3-L',
    'orientation' => 'L',
    'bleedMargin' => 0,
]);
$mpdf->SetDisplayPreferences('FullScreen');
    $mpdf->WriteHTML($final_doc);
    $mpdf->Output();


  }
}
