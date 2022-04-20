<?php

namespace App\BLoC\App\EventOrder;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\TransactionLog;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;

class GetEventOrderV2 extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();
        $status = $parameters->get('status');
        $output = [];

        $archery_event_official = ArcheryEventOfficial::where('user_id', $user_login->id)->orderBy('id', 'DESC');
        $archery_event_official->when($status, function ($query) use ($status) {
            if ($status == 'pending') {
                return $query->select('archery_event_official.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_official.transaction_log_id')
                    ->where('transaction_logs.status', 4)->where('transaction_logs.expired_time', '>', time());
            }
            if ($status == 'success') {
                return $query->where('archery_event_official.status', 1);
            }

            if ($status == 'expired') {
                return $query->select('archery_event_official.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_official.transaction_log_id')
                    ->where('transaction_logs.status', 4)->where('transaction_logs.expired_time', '<', time());
            }
        });

        $data = [];
        $col_archery_event_official = $archery_event_official->get();
        if ($col_archery_event_official->count() > 0) {
            foreach ($col_archery_event_official as $aeo) {
                $data['detail_order'] =[
                    "id"=>$aeo->id,
                    "type"=>"official"
                ];
                $data['transaction_log_info'] = TransactionLog::getTransactionInfoByid($aeo->transaction_log_id);
                $data['detail_event']= ArcheryEvent::select('archery_events.*')->leftJoin('archery_event_official_detail','archery_events.id','=','archery_event_official_detail.event_id')
                                ->where('archery_event_official_detail.id',$aeo->event_official_detail_id)->get();
                $data['participant'] = [];
                $category_label = $aeo->team_category_id."-".$aeo->age_category_id."-".$aeo->competition_category_id."-".$aeo->distance_id."m";
            
                $data['category'] = [
                    "team_category_id"=>$aeo->team_category_id,
                    "age_category_id"=>$aeo->age_category_id,
                    "competition_category_id"=>$aeo->competition_category_id,
                    "distance_id"=>$aeo->distance_id,
                    "label"=>$category_label
                ];
                array_push($output, $data);
            }
        }

        $participants = ArcheryEventParticipant::where("user_id", $user_login->id);
        $participants->when($status, function ($query) use ($status) {
            if ($status == 'pending') {
                return $query->select('archery_event_participants.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_participants.transaction_log_id')
                    ->where('transaction_logs.status', 4)->where('transaction_logs.expired_time', '>', time());
            }
            if ($status == 'success') {
                return $query->where('archery_event_participants.status', 1);
            }

            if ($status == 'expired') {
                return $query->select('archery_event_participants.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_participants.transaction_log_id')
                    ->where('transaction_logs.status', 4)->where('transaction_logs.expired_time', '<', time());
            }
        });

        $data_event = $participants->orderBy('archery_event_participants.id', 'desc')->get();

        if ($data_event->count() > 0) {
            foreach ($data_event as $aeo) {
                $data['detail_order'] =[
                    "id"=>$aeo->id,
                    "type"=>"event"
                ];
                $data['transaction_log_info'] = TransactionLog::getTransactionInfoByid($aeo->transaction_log_id);
                $detail_event= ArcheryEvent::find($aeo->event_id);
                $data['participant'] = $aeo;
                $category_label = $aeo->team_category_id."-".$aeo->age_category_id."-".$aeo->competition_category_id."-".$aeo->distance_id."m";
            
                $data['category'] = [
                    "team_category_id"=>$aeo->team_category_id,
                    "age_category_id"=>$aeo->age_category_id,
                    "competition_category_id"=>$aeo->competition_category_id,
                    "distance_id"=>$aeo->distance_id,
                    "label"=>$category_label
                ];
                array_push($output, $data);
            }
        }

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'status' => 'in:success,pending,expired'
        ];
    }
}
