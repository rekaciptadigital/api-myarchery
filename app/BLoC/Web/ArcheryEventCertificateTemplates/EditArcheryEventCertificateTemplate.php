<?php

namespace App\BLoC\Web\ArcheryEventCertificateTemplates;

use App\Models\ArcheryEventCertificateTemplates;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\DB;

class EditArcheryEventCertificateTemplate extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
      $event_id=$parameters->get("event_id");
      $type_certificate=$parameters->get("type_certificate");

      $archery_event_certificate_templates = DB::table('archery_event_certificate_templates')->where('event_id', $event_id)->where('type_certificate', $type_certificate)->first();
      $archery_event_certificate_templates->event_id = $parameters->get('event_id');
      $archery_event_certificate_templates->html_template = $parameters->get('html_template');
      $archery_event_certificate_templates->editor_data = $parameters->get('editor_data');
      $archery_event_certificate_templates->background_url = $parameters->get('background_url');
      $archery_event_certificate_templates->background_url = $parameters->get('type_certificate');

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
