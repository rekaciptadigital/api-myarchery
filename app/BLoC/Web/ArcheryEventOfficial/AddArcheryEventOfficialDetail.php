<?php

namespace App\BLoC\Web\ArcheryEventOfficial;

use App\Models\User;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficialDetail;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Mpdf\Output\Destination;

use Illuminate\Support\Facades\Storage;
use Knp\Snappy\Pdf;

class AddArcheryEventOfficialDetail extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        
      $admin = Auth::user();
      
      $ArcheryEventOfficialDetail = new ArcheryEventOfficialDetail();

      $event = ArcheryEvent::find($parameters->get('event_id'));
      if (!$event) {
          throw new BLoCException("event not found");
      }

      $ArcheryEventOfficialDetail->event_id = $parameters->get('event_id');
      $ArcheryEventOfficialDetail->fee = $parameters->get('fee');
      $ArcheryEventOfficialDetail->save();

      return $ArcheryEventOfficialDetail;
        
        
    }
    protected function validation($parameters)
    {
        return [
            'event_id' => [
                'required'
            ],
            'fee' => [
                'required'
            ],

        ];
    }

}