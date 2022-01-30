<?php

namespace App\BLoC\Web\ArcheryEventMoreInformation;

use App\Models\ArcheryEventMoreInformation;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class AddArcheryEventMoreInformation extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
      $admin = Auth::user();
      
      $archery_event_more_information = new ArcheryEventMoreInformation();

      $archery_event_more_information->event_id = $parameters->get('event_id');
      $archery_event_more_information->title =  $parameters->get('title');
      $archery_event_more_information->description = $parameters->get('description');
      $archery_event_more_information->save();

      return $archery_event_more_information;

    }

    protected function validation($parameters)
    {
      return [
          'event_id' => 'required',
          'title' => 'required',
          'description' => 'required',
      ];
    }
}
