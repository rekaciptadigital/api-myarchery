<?php

namespace App\BLoC\Web\BudRest;
ini_set('max_execution_time', 180);

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventIdcardTemplate;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventOfficial;
use App\Models\User;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use Illuminate\Support\Facades\Auth;

class GetIdCardByBudrest extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // param
        $date = $parameters->get("date");
        $event_id = $parameters->get("event_id");
        $admin = Auth::user();
        $type = $parameters->get("type") ?? 1; // 1 untuk peserta 2 untuk official

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $schedule_member_query = ArcheryEventQualificationScheduleFullDay::select(
            "archery_event_qualification_schedule_full_day.*",
            "archery_event_qualification_time.category_detail_id as category_id",
            "users.name as name",
            "users.id as user_id",
            "archery_event_participant_members.id as participant_member_id",
            "archery_clubs.name as club_name",
            "archery_clubs.id as club_id"
        )
            ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
            ->where("archery_event_participants.event_id", $event_id);

        if(!empty($date)) $schedule_member_query->whereDate("event_start_datetime", $date);       ;

        $schedule_member_collection = $schedule_member_query->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face")
            ->get();

        $output = [];
        $output["category_budrest"] = null;

        if ($schedule_member_collection->count() > 0) {
            foreach ($schedule_member_collection as $schedule) {
                $category = ArcheryEventCategoryDetail::find($schedule->category_id);
                if (!$category) {
                    throw new BLoCException("category tidak tersedia");
                }

                $output["category_budrest"][$category->id][] = [
                    "schedule_full_day_id" => $schedule->id,
                    "category_id" => $category->id,
                    "label_category" => $category->label_category,
                    "bud_rest_number" => $schedule->bud_rest_number === 0 ? "" : $schedule->bud_rest_number . "" . $schedule->target_face,
                    "name" => $schedule->name,
                    "user_id" => $schedule->user_id,
                    "participant_member_id" => $schedule->participant_member_id,
                    "club_id" => $schedule->club_id,
                    "club_name" => $schedule->club_name
                ];
            }
        }

        $data_collection = collect($output['category_budrest']);
        $data_sorted = $data_collection->sortKeys();

        $final_doc = [];

        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $event_id)->first();
        if (!$idcard_event) {
            throw new BLoCException("Template event id card tidak ditemukan");
        }

        $html_template = base64_decode($idcard_event->html_template);
        $background = $idcard_event->background;
        $logo = !empty($idcard_event->logo_event) ? $idcard_event->logo_event : "https://i.ibb.co/pXx14Zr/logo-email-archery.png";
        $location_and_date_event = $event->location_date_event;

        if ($type == 1) {
            $status = "Peserta";
            $final_doc = $this->generateArrayParticipant($data_sorted, $location_and_date_event, $background, $html_template, $logo, $status, $type, $event_id);
        }

        if ($idcard_event->editor_data == " " || $idcard_event->editor_data == null) throw new BLoCException("ID Card bantalan belom diset, silahkan konfigurasi di menu ID Card");
        $editor_data = json_decode($idcard_event->editor_data);
        $paper_size = $editor_data->paperSize;
        $orientation = array_key_exists("orientation", $editor_data) ? $editor_data->orientation : "P";

        if(!empty($date)){
            $file_name = $type == 1 ? "asset/idcard/idcard_" . $event->event_name . "_budrest_" . $date . ".pdf" : "asset/idcard/idcard_" . $category_file  . ".pdf";
        } else {
            $file_name = $type == 1 ? "asset/idcard/idcard_" . $event->event_name . "_budrest_allday" . ".pdf" : "asset/idcard/idcard_" . $category_file  . ".pdf";
        }

        PdfLibrary::setArrayDoc($final_doc['doc'])->setFileName($file_name)->savePdf(null, $paper_size, $orientation);
        return [
            "file_name" => env('APP_HOSTNAME') . $file_name,
            // "file_base_64" => env('APP_HOSTNAME') . $generate_idcard,
        ];
    }

    protected function validation($parameters)
    {
        $validator = [
            'event_id' => 'required',
            // 'date' => 'required'
        ];

        return $validator;
    }

    private function generateArrayParticipant($datas, $location_and_date_event, $background, $html_template, $logo, $status, $type, $event_id)
    {
        $final_doc = [];

        foreach($datas as $details) {

            foreach($details as $detail) {

                $user = User::find($detail['user_id']);
                if (!$user) {
                    continue;
                    // throw new BLoCException("user not found");
                }

                $gender = "";
                if ($user->gender != null) {
                    if ($user->gender == "male") {
                        $gender = "Laki-Laki";
                    } else {
                        $gender = "Perempuan";
                    }
                }

                $qr_code_data = $event_id . " " . $type . "-" . $detail['participant_member_id'];
                $avatar = !empty($user->avatar) ? $user->avatar : "https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png";

                $final_doc['doc'][] = str_replace(
                    ['{%player_name%}', '{%avatar%}', '{%category%}', '{%club_member%}', "{%background%}", '{%logo%}', '{%location_and_date%}', '{%certificate_verify_url%}', '{%status_event%}', '{%budrest_number%}', '{%gender%}'],
                    [$detail['name'], $avatar, $detail['label_category'], $detail['club_name'], $background, $logo, $location_and_date_event, $qr_code_data, $status, $detail['bud_rest_number'], $gender],
                    $html_template
                );

            }   
            
        }

        return $final_doc;
    }

}
