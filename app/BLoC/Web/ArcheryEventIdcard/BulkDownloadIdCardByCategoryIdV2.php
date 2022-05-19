<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

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

class BulkDownloadIdCardByCategoryIdV2 extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $team_category_id = $parameters->get('team_category_id');
        $age_category_id = $parameters->get('age_category_id');
        $competition_category_id = $parameters->get('competition_category_id');
        $distance_id = $parameters->get('distance_id');
        $event_id = $parameters->get('event_id');
        $type = $parameters->get("type"); // 1 untuk peserta 2 untuk official

        $archery_event = ArcheryEvent::find($event_id);
        if (!$archery_event) {
            throw new BLoCException("event tidak tersedia tersedia");
        }

        $status = "";

        $category = ArcheryEventCategoryDetail::where('team_category_id', $team_category_id)
            ->where("age_category_id", $age_category_id)
            ->where("competition_category_id", $competition_category_id)
            ->where("distance_id", $distance_id)
            ->where("event_id", $event_id)
            ->first();

        if (!$category) {
            throw new BLoCException("category not found");
        }

        if ($archery_event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $final_doc = [];

        $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($category->id);

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
            $final_doc = $this->generateArrayParticipant($category->id, $categoryLabel, $location_and_date_event, $background, $html_template, $logo, $status);
        } elseif ($type == 2) {
            $status = "Official";
            $final_doc = $this->generateArrayOfficial($team_category_id, $age_category_id, $competition_category_id, $distance_id, $event_id, $categoryLabel, $location_and_date_event, $background, $html_template, $logo, $status);
        }

        $editor_data = json_decode($idcard_event->editor_data);
        $paper_size = $editor_data->paperSize;
        $category_file = str_replace(' ', '', $categoryLabel);
        $file_name = "asset/idcard/idcard_" . $category_file . "_" . $category->id . ".pdf";
        $generate_idcard = PdfLibrary::setArrayDoc($final_doc)->setFileName($file_name)->savePdf(null, $paper_size, "P");

        $number = "MA-22-91-1-001";
        $array_number = explode("-", $number);
        $sequence = $array_number[count($array_number) - 1];
        $prefix = $array_number[0] . "-" . $array_number[1] . "-" . $array_number[2] . "-" . $array_number[3];

        $athlete = ArcheryEventParticipantNumber::where("prefix", $prefix)->where("sequence", (int)$sequence)->first();

        // return $prefix;
        // return $athlete;
        // return (int)$sequence;



        return [
            "file_name" => env('APP_HOSTNAME') . $file_name,
            "file_base_64" => env('APP_HOSTNAME') . $generate_idcard,
        ];
    }

    protected function validation($parameters)
    {
        $validator = [
            'event_id' => 'required',
            'type' => 'required',
            'team_category_id' => 'required',
            'age_category_id' => 'required',
            'competition_category_id' => 'required',
            'distance_id' => 'required'
        ];

        return $validator;
    }

    private function generateArrayParticipant($category_id, $categoryLabel, $location_and_date_event, $background, $html_template, $logo, $status)
    {
        $participants = ArcheryEventParticipant::where("event_category_id", $category_id)->where("status", 1)->get();
        if ($participants->isEmpty()) {
            throw new BLoCException("tidak ada partisipan");
        }

        $final_doc = [];

        foreach ($participants as $participant) {
            $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
            if (!$member) {
                throw new BLoCException("tidak ada data tersedia");
            }

            $user = User::find($member->user_id);
            if (!$user) {
                throw new BLoCException("user not found");
            }

            $number = $member->id;
            $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $member->id)->first();
            if (!$schedule) {
                throw new BLoCException("schedule not found");
            }

            $club = ArcheryClub::find($participant->club_id);
            if (!$club) {
                $club = '';
            } else {
                $club = $club->name;
            }

            $avatar = !empty($user->avatar) ? $user->avatar : "https://i0.wp.com/eikongroup.co.uk/wp-content/uploads/2017/04/Blank-avatar.png?ssl=1";

            $final_doc[] = str_replace(
                ['{%player_name%}', '{%avatar%}', '{%category%}', '{%club_member%}', "{%background%}", '{%logo%}', '{%location_and_date%}', '{%certificate_verify_url%}', '{%status_event%}'],
                [$user->name, $avatar, $categoryLabel, $club, $background, $logo, $location_and_date_event, $number, $status],
                $html_template
            );
        }
        return $final_doc;
    }

    private function generateArrayOfficial($team_category_id, $age_category_id, $competition_category_id, $distance_id, $event_id, $categoryLabel, $location_and_date_event, $background, $html_template, $logo, $status)
    {
        $official = ArcheryEventOfficial::select("archery_event_official.*")
            ->join("archery_event_official_detail", "archery_event_official_detail.id", "=", "archery_event_official.event_official_detail_id")
            ->where("archery_event_official.status", 1)
            ->where("archery_event_official.team_category_id", $team_category_id)
            ->where("archery_event_official.age_category_id", $age_category_id)
            ->where("archery_event_official.competition_category_id", $competition_category_id)
            ->where("archery_event_official.distance_id", $distance_id)
            ->where("archery_event_official_detail.event_id", $event_id)
            ->get();

        if ($official->count() == 0) {
            throw new BLoCException("tidak ada partisipan");
        }

        foreach ($official as $o) {
            $user = User::find($o->user_id);
            if (!$user) {
                throw new BLoCException("user not found");
            }

            $data_qr = $o->id;

            $club = ArcheryClub::find($o->club_id);
            if (!$club) {
                $club = '';
            } else {
                $club = $club->name;
            }

            $avatar = !empty($user->avatar) ? $user->avatar : "https://i0.wp.com/eikongroup.co.uk/wp-content/uploads/2017/04/Blank-avatar.png?ssl=1";

            $final_doc[] = str_replace(
                ['{%player_name%}', '{%avatar%}', '{%category%}', '{%club_member%}', "{%background%}", '{%logo%}', '{%location_and_date%}', '{%certificate_verify_url%}', '{%status_event%}'],
                [$user->name, $avatar, $categoryLabel, $club, $background, $logo, $location_and_date_event, $data_qr, $status],
                $html_template
            );
        }
        return $final_doc;
    }
}
