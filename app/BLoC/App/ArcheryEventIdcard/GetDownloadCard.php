<?php

namespace App\BLoC\App\ArcheryEventIdcard;

use App\Models\ParticipantMemberTeam;
use App\Models\ArcheryEventIdcardTemplate;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryClub;

class GetDownloadCard extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant_member_team = $parameters->get('participant_member_team');
        $user = Auth::guard('app-api')->user();

        $find_participant_member_team = ParticipantMemberTeam::select('users.name', 'archery_event_participants.event_id', 'archery_event_participants.user_id', 'participant_member_teams.participant_id', 'archery_event_participants.club_id')
            ->where('participant_member_teams.id', $participant_member_team)
            ->leftJoin("archery_event_participants", "archery_event_participants.id", "participant_member_teams.participant_id")
            ->leftJoin("users", "users.id", "archery_event_participants.user_id")
            ->first();

        if (!$find_participant_member_team) {
            throw new BLoCException("Data partisipan member team tidak ditemukan");
        }

        if ($find_participant_member_team->user_id != $user['id']) {
            throw new BLoCException("Data partisipan member team bukan milik dari user yang login");
        }

        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $find_participant_member_team->event_id)->first();
        if (!$idcard_event) {
            throw new BLoCException("Template event id card tidak ditemukan");
        }

        $category = ArcheryEventIdcardTemplate::getCategoryLabel($find_participant_member_team->participant_id, $find_participant_member_team->user_id);
        if ($category == "") {
            throw new BLoCException("Kategori tidak ditemukan");
        }

        $prefix = ArcheryEventIdcardTemplate::setPrefix($find_participant_member_team->participant_id, $find_participant_member_team->event_id);
        if ($prefix == "") {
            throw new BLoCException("Prefix gagal digenerate");
        }

        $club = ArcheryClub::find($find_participant_member_team->club_id);
        if (!$club) {
            $club = '';
        } else {
            $club = $club->name;
        }

        $html_template = base64_decode($idcard_event->html_template);

        if (!$idcard_event->background) {
            $background = '';
        } else {
            $background = 'background:url("' . $idcard_event->background . '")';
        }

        if (!$idcard_event->logo_event) {
            $logo = '<div id="logo" style="padding:3px"></div>';
        } else {
            $logo = '<img src="' . $idcard_event->logo_event . '" alt="Avatar" style="float:left;width:40px">';
        }

        //dd($background);
        $final_doc = str_replace(
            ['{%member_name%}', '{%event_category%}', '{%club%}', "background:url('')", '<div></div>'],
            [$find_participant_member_team->name, $category, $club, $background, $logo],
            $html_template
        );

        $file_name = "idcard_" . $participant_member_team . ".pdf";

        $generate_idcard = PdfLibrary::setFinalDoc($final_doc)->setFileName($file_name)->generateIdcard();

        return [
            "file_name" => $file_name,
            "file_base_64" => $generate_idcard,
        ];
    }
}
