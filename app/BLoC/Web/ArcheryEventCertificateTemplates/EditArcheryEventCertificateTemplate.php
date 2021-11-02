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

      $event_id = $parameters->get('event_id');
      $html_template = $parameters->get('html_template');
      $background_url = $parameters->get('background_url');
      $editor_data = $parameters->get('editor_data');
      $type_certificate = $parameters->get('type_certificate');

      $query = ArcheryEventCertificateTemplates::updateCertificateByEventAndType($event_id, $html_template,$background_url,$editor_data,$type_certificate);
      return $query;

  }

  protected function validation($parameters)
  {
    return [
      'event_id' => 'required',
      'html_template' => 'required',
      'editor_data' => 'required',
      'type_certificate' => 'required',
      'background_url' => 'required',
    ];
  }
}
