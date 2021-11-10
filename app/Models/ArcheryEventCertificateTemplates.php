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

  protected static $type_certificates = [
    "partisipan" => "1",
    "juara" => "2",
    "eliminasi" => "3",

  ];
  public static function getTypeCertificate()
    {
        return self::$type_certificates;
    }
  public static function  getCertificateByEventAndType($event_id, $type_certificate)
  {
    $archery_event_certificate_templates =DB::table('archery_event_certificate_templates')
    ->where('event_id', $event_id)->where('type_certificate', $type_certificate)->first();
    if(!$archery_event_certificate_templates){
      return false;
    }else{
      return $archery_event_certificate_templates;
    }
  }
  public static function  checkElimination($member_id)
  {
    $check_elimination=DB::table('archery_event_elimination_members')
    ->select('position_qualification')
    ->where('member_id', $member_id)
    ->first();

    return $check_elimination;
  }
  public static function  getCategoryLabel($event_id,$user_id)
  {
    $label=DB::table('archery_event_participants')
    ->leftjoin('archery_master_team_categories', 'archery_master_team_categories.id', '=', 'archery_event_participants.team_category_id')
    ->leftjoin('archery_master_age_categories', 'archery_master_age_categories.id', '=', 'archery_event_participants.age_category_id')
    ->leftjoin('archery_master_competition_categories', 'archery_master_competition_categories.id', '=', 'archery_event_participants.competition_category_id')
    ->leftjoin('archery_master_distances', 'archery_master_distances.id', '=', 'archery_event_participants.distance_id')
    ->select("archery_master_team_categories.label as label_team_categories",
    "archery_master_age_categories.label as label_age_categories",
    "archery_master_competition_categories.label as label_competition_categories",
    "archery_master_distances.label as label_distance")
    ->where('archery_event_participants.event_id', $event_id)
    ->where('archery_event_participants.user_id', $user_id)
    ->first();

    if(!$label){
      return false;
    }else{
      return $label;
    }
  }
}
