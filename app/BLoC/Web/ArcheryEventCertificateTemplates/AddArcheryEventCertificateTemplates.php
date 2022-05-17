<?php

namespace App\BLoC\Web\ArcheryEventCertificateTemplates;

use App\Models\ArcheryEventCertificateTemplates;
use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;
use App\Libraries\Upload;
use App\Libraries\Common;

class AddArcheryEventCertificateTemplates extends Transactional
{
  public function getDescription()
  {
    return "";
  }

  protected function process($parameters)
  {
    $admin = Auth::user();
    $event_id = $parameters->get('event_id');
    $type_certificate = $parameters->get('type_certificate');
    // TODO check is own
    // $checkAdmin=ArcheryEvent::isOwnEvent($admin['id'],$event_id);
    // if(!$checkAdmin)throw new BLoCException("event tidak ditemukan");

    $archery_event_certificate_templates = ArcheryEventCertificateTemplates::where("event_id", $event_id)->where("type_certificate", $type_certificate)->first();

    if (!$archery_event_certificate_templates)
      $archery_event_certificate_templates = new ArcheryEventCertificateTemplates();

    $archery_event_certificate_templates->event_id = $event_id;
    $archery_event_certificate_templates->html_template =  $parameters->get('html_template');
    Common::removeDir(public_path() . "/asset/certificate/event_" . $event_id . "/" . $type_certificate . "/users");
    if ($parameters->get('background_img')) {
      $path = "asset/certificate/event_" . $event_id;
      if (!file_exists(public_path() . "/" . $path)) {
        mkdir(public_path() . "/" . $path, 0775);
      }
      $path = "asset/certificate/event_" . $event_id . "/" . $type_certificate;
      if (!file_exists(public_path() . "/" . $path)) {
        mkdir(public_path() . "/" . $path, 0775);
      }
      $path = "asset/certificate/event_" . $event_id . "/" . $type_certificate . "/asset";
      if (!file_exists(public_path() . "/" . $path)) {
        mkdir(public_path() . "/" . $path, 0775);
      }
      $bg_url = Upload::setPath($path . "/")->setFileName("bg_" . $type_certificate)->setBase64($parameters->get('background_img'))->save();
      $archery_event_certificate_templates->background_url = $bg_url;
    };
    $archery_event_certificate_templates->editor_data = $parameters->get('editor_data');
    $archery_event_certificate_templates->type_certificate = $type_certificate;
    $archery_event_certificate_templates->save();


    return $archery_event_certificate_templates;
  }

  protected function validation($parameters)
  {
    return [
      'event_id' => 'required',
      'html_template' => 'required',
      'editor_data' => 'required',
      'type_certificate' => 'required',
    ];
  }
}
