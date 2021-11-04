<?php

namespace App\BLoC\Web\ArcheryEventCertificateTemplates;

use App\Models\ArcheryEventCertificateTemplates;
use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class EditArcheryEventCertificateTemplate extends Transactional
{
  public function getDescription()
  {
    return "";
  }

  protected function process($parameters)
  {
    $admin = Auth::user();
    $event_id=$parameters->get('event_id');
    $type_certificate=$parameters->get('type_certificate');
    $checkAdmin=ArcheryEvent::isOwnEvent($admin['id'],$event_id);
    if(!$checkAdmin)throw new BLoCException("event tidak ditemukan");

    $archery_event_certificate_templates = ArcheryEventCertificateTemplates::where('event_id', $event_id)->where('type_certificate', $type_certificate)->firstOrFail();
    $archery_event_certificate_templates->html_template = $parameters->get('html_template');
    $archery_event_certificate_templates->event_id = $parameters->get('event_id');
    $archery_event_certificate_templates->background_url = $parameters->get('background_url');
    $archery_event_certificate_templates->editor_data = $parameters->get('editor_data');
    $archery_event_certificate_templates->type_certificate = $type_certificate;
    $archery_event_certificate_templates->save();

    return $archery_event_certificate_templates;
  }

  protected function validation($parameters)
  {
    return [
      'event_id' => 'required',
      'type_certificate' => 'required',
    ];
  }
}
