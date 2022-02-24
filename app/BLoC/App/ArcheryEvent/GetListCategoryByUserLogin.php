<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ClubMember;
use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetListCategoryByUserLogin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user =  $user = Auth::guard('app-api')->user();

        $event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$event) {
            throw new BLoCException("event not found");
        }

        $data = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            'archery_event_participants.id as participant_id',
            'archery_event_participants.user_id',
            'archery_event_participants.email',
            'archery_event_participants.phone_number',
            'archery_event_participants.age',
            'archery_event_participants.gender',
            'archery_event_participants.status',
            'archery_event_participants.transaction_log_id',
            'archery_event_participants.event_category_id',
            'archery_event_participants.team_name',
            'archery_event_participants.club_id')->join("archery_event_participants", "archery_event_participants.event_category_id", '=', 'archery_event_category_details.id')
            ->where('archery_event_category_details.event_id', $event->id)
            ->where("archery_event_participants.user_id", $user->id)
            ->where("archery_event_participants.status", 1)
            ->get();

        $data_all = [];
        if ($data->count() > 0) {
            foreach ($data as $d) {
                $category_team = ArcheryEventCategoryDetail::select(
                    "archery_event_category_details.*",
                    'archery_event_participants.id as participant_id',
                    'archery_event_participants.user_id',
                    'archery_event_participants.email',
                    'archery_event_participants.phone_number',
                    'archery_event_participants.age',
                    'archery_event_participants.gender',
                    'archery_event_participants.status',
                    'archery_event_participants.transaction_log_id',
                    'archery_event_participants.event_category_id',
                    'archery_event_participants.team_name',
                    'archery_event_participants.club_id')->join("archery_event_participants", "archery_event_participants.event_category_id", '=', 'archery_event_category_details.id')
                    ->where("archery_event_participants.age_category_id", $d->age_category_id)
                    ->where("archery_event_participants.competition_category_id", $d->competition_category_id)
                    ->where("archery_event_participants.distance_id", $d->distance_id)
                    ->where("archery_event_participants.type", "team")
                    ->where("archery_event_participants.club_id", $d->club_id)
                    ->first();

                if ($category_team) {
                    array_push($data_all, $category_team);
                }

                array_push($data_all, $d);
            }
        }

        $output = [];
        $output_category = [];

        if (count($data_all) > 0) {
            foreach ($data_all as $d) {
                $event_category = ArcheryEventCategoryDetail::find($d->event_category_id);
                $club = ArcheryClub::find($d->club_id);
                $transaction_log = TransactionLog::find($d->transaction_log_id);
                $history_qualification = null;
                if ($event_category->category_team == "Individual") {
                    $qualification_full_day = DB::table('archery_event_qualification_schedule_full_day')->select(
                        'archery_event_qualification_time.event_start_datetime as date_start',
                        'archery_event_qualification_time.event_end_datetime as date_end'
                    )->join('archery_event_qualification_time', 'archery_event_qualification_time.id', '=', 'archery_event_qualification_schedule_full_day.qalification_time_id')
                        ->where('archery_event_qualification_schedule_full_day.participant_member_id', $d->member_id)
                        ->first();

                    if ($qualification_full_day) {
                        $today = (Carbon::now())->toDateTimeString();
                        $carbon_start_date = Carbon::parse($qualification_full_day->date_start);

                        if ($carbon_start_date < $today) {
                            $history_qualification = "kualifikasi selesai";
                        } else {
                            $history_qualification = "menunggu kualifikasi";
                        }
                    }
                }


                $club_detail = [];
                if ($club != null) {
                    $club_detail = [
                        "club_id" => $club->id,
                        "club_name" => $club->name,
                        "club_logo" => $club->logo,
                        "club_banner" => $club->banner,
                        "club_place_name" => $club->place_name,
                        "club_address" => $club->address,
                        "club_description" => $club->description,
                        "detail_province" => $club->detail_province,
                        "detail_city" => $club->detail_city
                    ];
                }

                $event_categoriy_data = $event_category->getCategoryDetailById($event_category->id);
                $event_categoriy_data['detail_participant'] = [
                    "id_participant" => $d->id,
                    "user_id" => $d->user_id,
                    "email" => $d->email,
                    "phone_number" => $d->phone_number,
                    "age" => $d->age,
                    "gender" => $d->gender,
                    "status" => $d->status,
                    "team_name" => $d->team_name,
                    "order_id" => $transaction_log ? $transaction_log->order_id : null,
                    "club_detail" => $club_detail,
                    "history_qualification" => $event_category->category_team == "Individual" ? $history_qualification : null
                ];
                array_push($output_category, $event_categoriy_data);
            }
        }

        $output['event_detail'] = $event->getDetailEventById($event->id);
        $output['category_detail'] = $output_category;

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer'
        ];
    }
}
