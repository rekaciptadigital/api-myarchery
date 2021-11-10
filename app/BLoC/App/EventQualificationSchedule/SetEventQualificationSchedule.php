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

class SetEventQualificationSchedule extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_session = ArcheryQualificationSchedules::where("date",$parameters->date)
        ->where("qualification_detail_id",$parameters->session_id)
        ->where("participant_member_id",$parameters->participant_member_id)
        ->first();
        if($user_session)throw new BLoCException("sesi sudah pernah dipilih, silahkan pilih sesi lain");
        $date1=date_create($parameters->date);
        $date2=date_create(date("Y-m-d H:i"));
        $day = \strtolower($date1->format("l"));
        $user = Auth::guard('app-api')->user();
        $user_id = $user["id"];
        
        $member = ArcheryEventParticipantMember::find($parameters->participant_member_id);
        if(!$member)throw new BLoCException("member tidak ditemukan");

        $session = ArcheryEventQualificationDetail::find($parameters->session_id);
        if(!$session)throw new BLoCException("sesi tidak ditemukan");
        
        $total_schedule_booking = ArcheryQualificationSchedules::where("qualification_detail_id",$parameters->session_id)
                                    ->where("date",$parameters->date)
                                    ->count();
        if($total_schedule_booking >= $session->quota)throw new BLoCException("sesi sudah penuh, silahkan pilih sesi lain");
        $qualification = ArcheryEventQualification::find($session->event_qualification_id);
        if($day != $qualification->day_id)throw new BLoCException("sesi tidak sesuai");

        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        
        if($user_id != $participant->user_id)throw new BLoCException("anda tidak dapat set sesi member ini");
        

        $transaction_info = PaymentGateWay::transactionLogPaymentInfo($participant->transaction_log_id);
        if($participant->status != 1)throw new BLoCException("pembayaran belum selesai");
        
        if($qualification->event_id != $participant->event_id)throw new BLoCException("pastikan event udah di ikuti");

        $date1=date_create($parameters->date." ".$session->start_time);
        if($date1 < $date2)throw new BLoCException("jadwal sudah lewat");

        $check_user_session = ArcheryQualificationSchedules::where("participant_member_id",$member->id)->count();
 
        if($check_user_session >=3 )throw new BLoCException("hanya bisa pilih 3 sesi");

        $user_session = new ArcheryQualificationSchedules;
        $user_session->date = $parameters->date;
        $user_session->qualification_detail_id = $parameters->session_id;
        $user_session->participant_member_id = $parameters->participant_member_id;
        $user_session->save();

        return $user_session;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
