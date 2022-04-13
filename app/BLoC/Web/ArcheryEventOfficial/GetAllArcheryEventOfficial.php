<?php

namespace App\BLoC\Web\ArcheryEventOfficial;

use App\Models\User;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficialDetail;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Mpdf\Output\Destination;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventOfficial;

class GetAllArcheryEventOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $name =$parameters->get('name');
        
        $event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$event) {
            throw new BLoCException("event not found");
        }

        //hitung jumlah tersedia disini


        $official_member = ArcheryEventOfficial::select('users.name as user_name','archery_clubs.name as club_name','users.email as email', 'users.phone_number as phone_number')
                            ->where('archery_event_official_detail.event_id', $parameters->get('event_id'))
                            ->leftJoin('archery_clubs','archery_clubs.id','=','archery_event_official.club_id')
                            ->leftJoin('users','users.id','=','archery_event_official.user_id')
                            ->leftJoin('archery_event_official_detail','archery_event_official_detail.id','=','archery_event_official.event_official_detail_id')
                            ->where(function ($query) use ($name) {
                                if(!empty($name)){
                                    $query->where("users.name",$name);
                                }
                            })
                            ->get();

        if ($official_member->isEmpty()) {
            throw new BLoCException("data not found");
        }

        foreach($official_member as $member){
            $data['data']=$member;
        }
        
        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => [
                'required'
            ],

        ];
    }
}
