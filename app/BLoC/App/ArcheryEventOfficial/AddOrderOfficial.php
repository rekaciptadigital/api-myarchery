<?php

namespace App\BLoC\App\ArcheryEventOfficial;

use App\Libraries\PaymentGateWay;
use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\User;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;


class AddOrderOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();

        $club_id = $parameters->get('club_id');
        $event_id = $parameters->get('event_id');
        $team_category_id = $parameters->get('team_category_id');
        $age_category_id = $parameters->get('age_category_id');
        $competition_category_id = $parameters->get('competition_category_id');
        $team_name = $parameters->get('team_name');
        $participant_data = $parameters->get('participant_data');
        
        $time_now = time();

        

        // cek apakah club yang diinputkan user terdapat di db
        if ($club_id != 0) {
            $club = ArcheryClub::find($club_id);
            if (!$club) {
                throw new BLoCException("club tidak ditemukan");
            }
        }

        // cek apakah event yang diinputkan terdapat di db
        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        // cek apakah event telah berlangsung atau belum
        $now = Carbon::now();
        $new_format_registration_start = Carbon::parse($event->registration_start_datetime, new DateTimeZone('Asia/jakarta'));
        $new_format_registration_end = Carbon::parse($event->registration_end_datetime, new DateTimeZone('Asia/jakarta'));

        if($now < $new_format_registration_start){
            throw new BLoCException("event belum bisa di daftar");
        }

        if($now > $new_format_registration_end){
            throw new BLoCException("pendaftaran event telah lewat");
        }

        
        // cek apakah terdapat official detail tersedia
        $archery_event_official_detail =  ArcheryEventOfficialDetail::where('event_id', $event_id)->first();
        if (!$archery_event_official_detail) {
            throw new BLoCException("kategori official pada event ini belum di atur");
        }
        
        
        if(!$participant_data){
            $participant_data=[$user_login->id];
        }

        foreach($participant_data as $user_id){
            $user_details=User::find($user_id);

            if ($club_id != 0) {
                $club_member = ClubMember::where('club_id', $club_id)->where('user_id', $user_id)->first();
                    if (!$club_member) {
                        throw new BLoCException("user dengan email ".$user_details->email." belum tergabung pada club tersebut");
                    }
            } 

            // cek jika telah terdaftar sebagai official
            $is_exist = ArcheryEventOfficial::select('archery_event_official.*', 'transaction_logs.status as status_transaction_log', 'transaction_logs.expired_time')
            ->join('archery_event_official_detail', 'archery_event_official_detail.id', '=', 'archery_event_official.event_official_detail_id')
            ->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_official.transaction_log_id')
            ->where('user_id', $user_id)
            ->where('archery_event_official_detail.event_id', $event_id)
            ->get();

            if ($is_exist->count() > 0) {
                foreach ($is_exist as $value) {
                    if ($value->status == 1) {
                        throw new BLoCException("user dengan email ".$user_details->email." telah terdaftar sebagai anggota official pada event ini");
                    } elseif ($value->status_transaction_log == 4 && $value->expired_time > $time_now) {
                        throw new BLoCException("user dengan email ".$user_details->email. " Transaksinya sedang berlangsung, mohon selesaikan transaksinya");
                    }
                }
            }
        }
        

        
        // hitung jumlah official pada category yang didaftarkan user
        $official_count = ArcheryEventOfficial::countEventOfficialBooking($archery_event_official_detail->id);
        
        if($archery_event_official_detail->individual_quota !=0){
            $quota= $archery_event_official_detail->individual_quota;
        }else{
            $quota= $archery_event_official_detail->club_quota;
        }

        if ($official_count >= $quota) {
            $msg = "quota official sudah penuh";
            // check kalo ada pembayaran yang pending
            $official_count_pending = ArcheryEventOfficial::join("transaction_logs", "transaction_logs.id", "=", "archery_event_official.transaction_log_id")
                ->join('archery_event_official_detail', 'archery_event_official_detail.id', '=', 'archery_event_official.event_official_detail_id')
                ->where("event_official_detail_id", $archery_event_official_detail->id)
                ->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", ">", $time_now)
                ->where("archery_event_official_detail.event_id", $archery_event_official_detail->event_id)->count();

            if ($official_count_pending > 0) {
                $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
            } else {
                $msg = $msg . ", silahkan daftar di event lain";
            }
            throw new BLoCException($msg);
        }else{
            $remaining_quota= $quota - $official_count;
            if(count($participant_data)>=$remaining_quota){
                throw new BLoCException("sisa kuota pendaftaran tidak mencukupi");
            }
        }
        

        $team_category_id = $parameters->get('team_category_id');
        $age_category_id = $parameters->get('age_category_id');
        $competition_category_id = $parameters->get('competition_category_id');
        $distance_id = $parameters->get('distance_id');
        // cek apakah fee gratis
        foreach($participant_data as $user_id){
            if ($archery_event_official_detail->fee < 1) {
                $archery_event_official =  ArcheryEventOfficial::insertOrderOfficial($user_id, $club_id, $team_category_id, $age_category_id, $competition_category_id, $distance_id,$archery_event_official_detail->id,1);
                return [
                    'archery_event_official' => $archery_event_official,
                    'payment_info' => null
                ];
            }

            $archery_event_official =  ArcheryEventOfficial::insertOrderOfficial($user_id, $club_id, $team_category_id, $age_category_id, $competition_category_id, $distance_id,$archery_event_official_detail->id);

        }
        
        $order_official_id = env("ORDER_OFFICIAL_ID_PREFIX", "OO-S") . $archery_event_official->id;

       
        $payment = PaymentGateWay::setTransactionDetail((int)$archery_event_official_detail->fee, $order_official_id)
            ->enabledPayments(["bca_va", "bni_va", "bri_va", "other_va", "gopay"])
            ->setCustomerDetails($user_login->name, $user_login->email, $user_login->phone_number)
            ->addItemDetail($archery_event_official_detail->id, (int)$archery_event_official_detail->fee, $event->event_name)
            ->createSnap();

        $archery_event_official->transaction_log_id = $payment->transaction_log_id;
        $archery_event_official->save();

        return [
            'archery_event_official' => $archery_event_official,
            'payment_info' => $payment
        ];
    }

    protected function validation($parameters)
    {
        return [
            'team_category_id' => 'required|string',
            "age_category_id" => "required|string",
            'competition_category_id' => 'required|string',
            "distance_id" => "required|integer",
            'event_id' => 'required|integer',
            'club_id' => 'required|integer'
        ];
    }
}
