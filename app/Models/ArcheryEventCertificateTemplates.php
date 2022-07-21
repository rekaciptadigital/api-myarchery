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
    "participant" => "1",
    "winner" => "2",
    "elimination" => "3",
    "qualification_winner" => "4",
    "team_qualification_winner" => "5",
    "mix_team_qualification_winner" => "6",
  ];

  protected static $type_certificate_label = [
    "1" => "Peserta",
    "2" => "Pemenang Eliminasi",
    "3" => "Eliminasi",
    "4" => "Kualifikasi",
    "5" => "Kualifikasi Beregu",
    "6" => "Kualifikasi Beregu Campuran",
  ];

  public static function getTypeCertificate()
  {
    return self::$type_certificates;
  }

  public static function getCertificateLabel($id)
  {
    foreach (self::$type_certificates as $key => $value) {
      if ($value == $id)
        return $key;
    };

    return "";
  }

  public static function getCertificateLabelByType($id)
  {
    return isset(self::$type_certificate_label[$id]) ? self::$type_certificate_label[$id] : "";
  }

  public static function getCertificateType($type)
  {
    return isset(self::$type_certificates[$type]) ? self::$type_certificates[$type] : "";
  }

  public static function  getCertificateByEventAndType($event_id, $type_certificate)
  {
    $archery_event_certificate_templates = DB::table('archery_event_certificate_templates')
      ->where('event_id', $event_id)->where('type_certificate', $type_certificate)->first();
    return $archery_event_certificate_templates;
  }
  public static function  checkElimination($member_id)
  {
    $check_elimination = DB::table('archery_event_elimination_members')
      ->where('member_id', $member_id)
      ->first();

    return $check_elimination;
  }

  // TODO update cara pengambilan category
  public static function  getCategoryLabel($participant_id, $user_id)
  {
    $category = DB::table('archery_event_participants')
      ->leftjoin('archery_master_team_categories', 'archery_master_team_categories.id', '=', 'archery_event_participants.team_category_id')
      ->leftjoin('archery_master_age_categories', 'archery_master_age_categories.id', '=', 'archery_event_participants.age_category_id')
      ->leftjoin('archery_master_competition_categories', 'archery_master_competition_categories.id', '=', 'archery_event_participants.competition_category_id')
      ->leftjoin('archery_master_distances', 'archery_master_distances.id', '=', 'archery_event_participants.distance_id')
      ->select(
        "archery_master_team_categories.label as label_team_categories",
        "archery_master_age_categories.label as label_age_categories",
        "archery_master_competition_categories.label as label_competition_categories",
        "archery_master_distances.label as label_distance"
      )
      ->where('archery_event_participants.id', $participant_id)
      ->where('archery_event_participants.user_id', $user_id)
      ->first();

    if (!$category) {
      return "";
    } else {
      return $category->label_team_categories . " - " . $category->label_age_categories . " - " . $category->label_competition_categories . " - " . $category->label_distance;
    }
  }
}
