<?php

namespace App\BLoC\Web\ArcheryEventOfficial;

use App\Models\User;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\ArcheryEventOfficial;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Mpdf\Output\Destination;
use Illuminate\Support\Facades\DB;

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

        $individual = $parameters->get('individual_quota');
        $club = $parameters->get('club_quota');

        $official_count = ArcheryEventOfficial::countEventOfficialBooking($parameters->get('id'));




        if ($individual != 0) {
            if ($individual < $official_count) {
                throw new BLoCException("nilai kuota tidak bisa diedit nilainya jika kurang dari total peserta yang sudah mendaftar");
            }
        } else {
            $count = ArcheryEventOfficial::select(DB::raw("COUNT(club_id) as count"))
                ->groupBy("archery_event_official.club_id")
                ->orderBy("archery_event_official.club_id", "DESC")
                ->limit(1)
                ->get();

            foreach (array($count) as $key => $counting) {
                $total = $counting[0]['count'];
            }
            if ($club < $total) {
                throw new BLoCException("nilai kuota tidak bisa diedit nilainya jika kurang dari total peserta yang sudah mendaftar");
            }
        }

        $ArcheryEventOfficialDetail = ArcheryEventOfficialDetail::find($parameters->get('id'));

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
