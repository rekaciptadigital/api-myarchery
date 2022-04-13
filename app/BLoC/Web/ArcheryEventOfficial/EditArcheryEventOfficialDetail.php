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

class EditArcheryEventOfficialDetail extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        
      $admin = Auth::user();
      
      $ArcheryEventOfficialDetail = ArcheryEventOfficialDetail::find($parameters->get('id'));
      #belum hitung kuota yang daftar
      $ArcheryEventOfficialDetail->event_id = $parameters->get('event_id');
      $ArcheryEventOfficialDetail->individual_quota =  $parameters->get('individual_quota');
      $ArcheryEventOfficialDetail->club_quota = $parameters->get('club_quota');
      $ArcheryEventOfficialDetail->fee = $parameters->get('fee');
      $ArcheryEventOfficialDetail->save();

      return $ArcheryEventOfficialDetail;
        
        
    }
    protected function validation($parameters)
    {
        return [
            'id' => [
                'required'
            ]
        ];
    }

}