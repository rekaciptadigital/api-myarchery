<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class ArcheryEventCertificateTemplates extends Model
{
    protected $fillable = [
        'event_id',
        'html_template',
        'background_url',
        'editor_data',
        'type_certificate',
    ];
    public static function  updateCertificateByEventAndType($event_id, $type_certificate, $data = array())
    {
        $archery_event_certificate_templates =DB::table('archery_event_certificate_templates')->where('event_id', $event_id)->where('type_certificate', $type_certificate)->update($data);
        $response = array('message' => 'success','data' => $archery_event_certificate_templates);
        return  $response;
    }
    public static function  getCertificateByEventAndType($event_id, $type_certificate)
    {
        $archery_event_certificate_templates =DB::table('archery_event_certificate_templates')->where('event_id', $event_id)->where('type_certificate', $type_certificate)->get();
        return  $archery_event_certificate_templates;
    }
    public static function  addCertificate($event_id, $html_template,$background_url,$editor_data,$type_certificate)
    {
      $archery_event_certificate_templates = new ArcheryEventCertificateTemplates();

      $archery_event_certificate_templates->event_id = $event_id;
      $archery_event_certificate_templates->html_template = $html_template;
      $archery_event_certificate_templates->background_url =$background_url;
      $archery_event_certificate_templates->editor_data = $editor_data;
      $archery_event_certificate_templates->type_certificate =$type_certificate;
      $archery_event_certificate_templates->save();

      return $archery_event_certificate_templates;
    }
}
