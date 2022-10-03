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

class GetIdCardByCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $category_id = $parameters->get("category_id");
        $event_id = $parameters->get('event_id');
        $date = $parameters->get('date');

        $type = $parameters->get("type") ?? 1; // 1 untuk peserta 2 untuk official

        $archery_event = ArcheryEvent::find($event_id);
        if (!$archery_event) {
            throw new BLoCException("event tidak tersedia tersedia");
        }

        if ($archery_event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $datas = ArcheryEventCategoryDetail::select('archery_event_category_details.id as archery_event_category_details_id', 'archery_event_category_details.event_id', 'archery_event_qualification_time.id as archery_event_qualification_time_id', 'archery_event_qualification_time.event_start_datetime')
                    ->where('event_id', $event_id)
                    ->join('archery_event_qualification_time', 'archery_event_qualification_time.category_detail_id', '=', 'archery_event_category_details.id')
                    ->where(function ($query) use ($date){
                        if(!empty($date)){
                            $query->whereDate('archery_event_qualification_time.event_start_datetime', $date);
                        } 
                    })->get();

        $final_doc = [];

        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $event_id)->first();
        if (!$idcard_event) {
            throw new BLoCException("Template event id card tidak ditemukan");
        }

        $html_template = base64_decode($idcard_event->html_template);
        $background = $idcard_event->background;
        $logo = !empty($idcard_event->logo_event) ? $idcard_event->logo_event : "https://i.ibb.co/pXx14Zr/logo-email-archery.png";
        $location_and_date_event = $archery_event->location_date_event;

        if ($type == 1) {
            $status = "Peserta";
            $final_doc = $this->generateArrayParticipant($datas, $location_and_date_event, $background, $html_template, $logo, $status, $type, $event_id);
        } elseif ($type == 2) {
            $status = "Official";
            $final_doc = $this->generateArrayOfficial($event_id, $location_and_date_event, $background, $html_template, $logo, $status, $type);
        }

        if ($idcard_event->editor_data == " " || $idcard_event->editor_data == null) throw new BLoCException("ID Card bantalan belom diset, silahkan konfigurasi di menu ID Card");
        $editor_data = json_decode($idcard_event->editor_data);
        $paper_size = $editor_data->paperSize;
        $orientation = array_key_exists("orientation", $editor_data) ? $editor_data->orientation : "P";
        
        if(!empty($date)){
            $file_name = $type == 1 ? "asset/idcard/idcard_" . $archery_event->event_name . "_category_" . $date . ".pdf" : "asset/idcard/idcard_" . $category_file  . ".pdf";
        } else {
            $file_name = $type == 1 ? "asset/idcard/idcard_" . $archery_event->event_name . "_category_allday" . ".pdf" : "asset/idcard/idcard_" . $category_file  . ".pdf";
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

    private function generateArrayParticipant(object $datas, $location_and_date_event, $background, $html_template, $logo, $status, $type, $event_id)
    {
        $final_doc = [];

        foreach($datas as $data) {
            $category = ArcheryEventCategoryDetail::find($data->archery_event_category_details_id);

            if (!$category) {
                throw new BLoCException("category not found");
            }

            if ($category->event_id != $event_id) {
                throw new BLoCException("forbiden");
            }

            $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($category->id);

            $participants = ArcheryEventParticipant::where("event_category_id", $data->archery_event_category_details_id)->where("status", 1)->orderBy('club_id')->get();
            if ($participants->isEmpty()) {
                continue;
                // throw new BLoCException("tidak ada partisipan");
            }

            foreach ($participants as $participant) {
                $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
                if (!$member) {
                    continue;
                    // throw new BLoCException("tidak ada data tersedia");
                }

                $user = User::find($member->user_id);
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

                $qr_code_data = $event_id . " " . $type . "-" . $member->id;
                $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $member->id)->first();
                $budrest_number = "";
                if ($schedule && $schedule->bud_rest_number != 0) {
                    $budrest_number = $schedule->bud_rest_number . $schedule->target_face;
                }

                $club = ArcheryClub::find($participant->club_id);
                if (!$club) {
                    $club = '';
                } else {
                    $club = $club->name;
                }

                $avatar = !empty($user->avatar) ? $user->avatar : "https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png";

                $final_doc['doc'][] = str_replace(
                    ['{%player_name%}', '{%avatar%}', '{%category%}', '{%club_member%}', "{%background%}", '{%logo%}', '{%location_and_date%}', '{%certificate_verify_url%}', '{%status_event%}', '{%budrest_number%}', '{%gender%}'],
                    [$user->name, $avatar, $categoryLabel, $club, $background, $logo, $location_and_date_event, $qr_code_data, $status, $budrest_number, $gender],
                    $html_template
                );
            }
            
        }

        return $final_doc;
    }

}
