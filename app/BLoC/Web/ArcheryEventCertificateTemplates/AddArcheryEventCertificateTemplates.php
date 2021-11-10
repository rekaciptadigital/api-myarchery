<?php

namespace App\BLoC\Web\ArcheryEventCertificateTemplates;

use App\Models\ArcheryEventCertificateTemplates;
use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class AddArcheryEventCertificateTemplates extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
      $admin = Auth::user();
      $event_id=$parameters->get('event_id');
      $checkAdmin=ArcheryEvent::isOwnEvent($admin['id'],$event_id);
      if(!$checkAdmin)throw new BLoCException("event tidak ditemukan");

      $archery_event_certificate_templates = new ArcheryEventCertificateTemplates();

      $archery_event_certificate_templates->event_id = $event_id;
      $archery_event_certificate_templates->html_template =  $parameters->get('html_template');
      $archery_event_certificate_templates->background_url = $parameters->get('background_url');
      $archery_event_certificate_templates->editor_data = $parameters->get('editor_data');
      $archery_event_certificate_templates->type_certificate =$parameters->get('type_certificate');
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
