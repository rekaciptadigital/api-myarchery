<?php

namespace App\BLoC\App\EventQualificationSchedule;


use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryQualificationSchedules;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventQualification;
use App\Models\ArcheryEventQualificationDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use Illuminate\Support\Facades\Auth;
use App\Libraries\PaymentGateWay;

class UnsetEventQualificationSchedule extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $date2=date_create(date("Y-m-d"));
        $user = Auth::guard('app-api')->user();
        $user_id = $user["id"];
        
        $user_session = ArcheryQualificationSchedules::find($parameters->schedule_id);
        
        if(!$user_session)throw new BLoCException("schedule tidak ditemukan");
        $date1=date_create($user_session->date);
        $session = ArcheryEventQualificationDetail::find($user_session->qualification_detail_id);

        if($date2 <= $date1){
            $diff=date_diff($date1,$date2);
            if($diff->format("%a") <= 2)throw new BLoCException("jadwal tidak bisa dibatalkan, pembatalan paling lambat sebelum 2 hari penjadwalan");
        }
        if(date_create($user_session->date." ".$session->start_time) < date_create(date("Y-m-d H:i")))throw new BLoCException("jadwal sudah lewat");


        $member = ArcheryEventParticipantMember::find($user_session->participant_member_id);
        if(!$member)throw new BLoCException("member tidak ditemukan");

        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        
        if($user_id != $participant->user_id)throw new BLoCException("anda tidak dapat unset sesi member ini");
        
        $user_session->delete();

        return $user_session;

        return $schedule;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
