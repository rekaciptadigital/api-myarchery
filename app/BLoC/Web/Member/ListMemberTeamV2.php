<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\TransactionLog;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class ListMemberTeamV2 extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page')? $parameters->get('page') : 1;
        $offset = ($page - 1) * $limit;
        $event_id = $parameters->get("event_id");
        $competition_category_id = $parameters->get("competition_category_id");
        $distance_id = $parameters->get("distance_id");
        $team_category_id = $parameters->get("team_category_id");
        $age_category_id = $parameters->get("age_category_id");
        $type = $parameters->get("type");

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("Event tidak tersedia");
        }
        
        $participant_query = ArcheryEventParticipant::where("event_id", $event_id)->where("type", "team");
        

        // filter by competition_id
        $participant_query->when($competition_category_id, function ($query) use ($competition_category_id) {
            return $query->where("competition_category_id", $competition_category_id);
        });

        // filter by distance_id
        $participant_query->when($distance_id, function ($query) use ($distance_id) {
            return $query->where("distance_id", $distance_id);
        });

        // filter by team_category_id
        $participant_query->when($team_category_id, function ($query) use ($team_category_id) {
            return $query->where("team_category_id", $team_category_id);
        });

        // filter by age_category_id
        $participant_query->when($age_category_id, function ($query) use ($age_category_id) {
            return $query->where("age_category_id", $age_category_id);
        });

        $participant_collection = $participant_query->orderBy('id', 'DESC')->limit($limit)->offset($offset)->get();
        //dd($participant_collection);
        
        $detail_member = [];
        
        foreach ($participant_collection as $participant) {
            
            $club = ArcheryClub::find($participant->club_id);
            $club_name = $club ? $club->name : "";

            $transaction_log = TransactionLog::find($participant->transaction_log_id);
            $status_payment = "";
            if ($transaction_log) {
                if ($transaction_log->status == 1) {
                    $status_payment = "Lunas";
                }

                if (($transaction_log->status == 4) && ($transaction_log->expired_time >= time())) {
                    $status_payment = "Belum Lunas";
                }

                if (($transaction_log->status == 2) || ($transaction_log->status == 4) && ($transaction_log->expired_time <= time())) {
                    $status_payment = "Expired";
                }
            } else {
                $status_payment = "Gratis";
            }
            
            $detail_member[]=["participant_id"=> $participant->id,
                            "user_id" => $participant->user_id,
                            "name"=> $participant->name,
                            "email"=>$participant->email,
                            "club_name"=> $club_name,
                            "phone_number"=>$participant->phone_number,
                            "competition_category"=> $participant->competition_category_id,
                            "status_payment"=>$status_payment,
                            "age_category"=> $participant->age_category_id];
            
            }

        return $detail_member;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
        ];
    }
}
