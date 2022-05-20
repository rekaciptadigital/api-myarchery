<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventIdcardTemplate;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\User;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class FindIdCardByMmeberOrOfficialId extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get('event_id');
        $code = $parameters->get("code");

        $array_code = explode("-", $code);
        if (count($array_code) != 2) {
            throw new BLoCException("code invalid");
        }

        $type = $array_code[0];
        if ($type == 0 || $type > 2) {
            throw new BLoCException("TYPE INVALID");
        }

        $member_id = $array_code[1];

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($admin->id != $event->admin_id) {
            throw new BLoCException("forbiden");
        }

        $id_card_template = ArcheryEventIdcardTemplate::where("event_id", $event_id)->first();
        if (!$id_card_template) {
            throw new BLoCException("template tidak tersedia");
        }

        $background = $id_card_template->background;
        $location_and_date_event = $event->location_date_event;
        $logo = !empty($id_card_template->logo_event) ? $id_card_template->logo_event : "https://i.ibb.co/pXx14Zr/logo-email-archery.png";
        $html_template = base64_decode($id_card_template->html_template);

        if ($type == 1) {
            $final_doc = $this->findParticipantIdcard($member_id, $type, $background, $location_and_date_event, $logo, $html_template);
        } elseif ($type == 2) {
            $final_doc = $this->findOfficialIdCard($member_id, $type, $event_id, $background, $logo, $location_and_date_event, $html_template);
        }

        return [
            "base_64_html_template" => $final_doc
        ];
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => "required",
            'code' => 'required'
        ];
    }

    private function findParticipantIdcard($member_id, $type, $background, $location_and_date_event, $logo, $html_template)
    {
        $status = "Peserta";
        $member = ArcheryEventParticipantMember::find($member_id);
        if (!$member) {
            throw new BLoCException("member tidak ditemukan");
        }

        $user = User::find($member->user_id);
        if (!$user) {
            throw new BLoCException("user not found");
        }

        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        if (!$participant) {
            throw new BLoCException("participant not found");
        }

        $category = ArcheryEventCategoryDetail::find($participant->event_category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($category->id);

        $qr_code_data = $type . "-" . $member->id;

        $club = ArcheryClub::find($participant->club_id);
        if (!$club) {
            $club = '';
        } else {
            $club = $club->name;
        }

        $avatar = !empty($user->avatar) ? $user->avatar : "https://i0.wp.com/eikongroup.co.uk/wp-content/uploads/2017/04/Blank-avatar.png?ssl=1";

        $final_doc = str_replace(
            ['{%player_name%}', '{%avatar%}', '{%category%}', '{%club_member%}', "{%background%}", '{%logo%}', '{%location_and_date%}', '{%certificate_verify_url%}', '{%status_event%}'],
            [$user->name, $avatar, $categoryLabel, $club, $background, $logo, $location_and_date_event, $qr_code_data, $status],
            $html_template
        );

        $base64_template = base64_encode($final_doc);

        return $base64_template;
    }

    private function findOfficialIdCard($member_id, $type, $event_id, $background, $logo, $location_and_date_event, $html_template)
    {
        $status = "Official";
        $official = ArcheryEventOfficial::find($member_id);
        if (!$official) {
            throw new BLoCException("Official not found");
        }

        $user = User::find($official->user_id);
        if (!$user) {
            throw new BLoCException("user not found");
        }

        $data_qr = $type . "-" . $official->id;

        $club = ArcheryClub::find($official->club_id);
        if (!$club) {
            $club = '';
        } else {
            $club = $club->name;
        }

        $category = ArcheryEventCategoryDetail::where("event_id", $event_id)
            ->where("age_category_id", $official->age_category_id)
            ->where("competition_category_id", $official->competition_category_id)
            ->where("distance_id", $official->distance_id)
            ->where("team_category_id", $official->team_category_id)
            ->first();

        if (!$category) {
            throw new BLoCException("category not found");
        }

        $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($category->id);

        $avatar = !empty($user->avatar) ? $user->avatar : "https://i0.wp.com/eikongroup.co.uk/wp-content/uploads/2017/04/Blank-avatar.png?ssl=1";

        $final_doc = str_replace(
            ['{%player_name%}', '{%avatar%}', '{%category%}', '{%club_member%}', "{%background%}", '{%logo%}', '{%location_and_date%}', '{%certificate_verify_url%}', '{%status_event%}'],
            [$user->name, $avatar, $categoryLabel, $club, $background, $logo, $location_and_date_event, $data_qr, $status],
            $html_template
        );

        $base64_template = base64_encode($final_doc);

        return $base64_template;
    }
}
