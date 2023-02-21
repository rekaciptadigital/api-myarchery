<?php

namespace App\BLoC\Web\EliminationScoreSheet;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Models\ArcheryEventParticipant;
use App\Models\City;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class DownloadEliminationScoreSheet extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $elimination_id = $parameters->get('event_elimination_id');
        $round = $parameters->get('round');
        $match = $parameters->get('match');
        $category_id = $parameters->get("category_id");

        $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();

        if (!$category) {
            throw new BLoCException("category not found");
        }

        $archery_event = ArcheryEvent::find($category->event_id);
        if (!$archery_event) {
            throw new BLoCException("event not found");
        }

        $event_name = $archery_event->event_name;
        $location_event = $archery_event->location;
        if (strtolower($category->type) == "team") {
            return $this->getTeam($elimination_id, $round, $match, $category_id, $event_name, $location_event, $archery_event->with_contingent);
        } else {
            return $this->getMember($elimination_id, $round, $match, $category_id, $event_name, $location_event, $archery_event->with_contingent);
        }
    }

    protected function validation($parameters)
    {
        return [
            'event_elimination_id' => 'required',
            'round' => 'required',
            'match' => 'required',
            'category_id' => 'required'
        ];
    }

    private function getMember($elimination_id, $round, $match, $category_id, $event_name, $location_event, $with_contingent)
    {
        $elimination = ArcheryEventElimination::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination not found");
        }
        $data_member = ArcheryEventEliminationMatch::where('event_elimination_id', $elimination_id)
            ->where('round', $round)
            ->where('match', $match)
            ->get();

        if ($data_member->count() == 0) {
            throw new BLoCException("data not found");
        }

        $string_code = "2-" . $data_member[0]->event_elimination_id . "-" . $data_member[0]->match . "-" . $data_member[0]->round;
        $path = 'asset/score_sheet/' . $category_id  . '/';
        if (!file_exists(public_path() . "/" . $path)) {
            mkdir(public_path() . "/" . $path, 0777);
        }
        $qrCode = new QrCode($string_code);

        $output_qrcode = new Output\Png();

        $qrCode_name_file = "qr_code_" . $string_code . ".png";

        $full_path = $path . $qrCode_name_file;

        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);

        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);

        foreach ($data_member as $data) {
            $name = "";
            $rank = "";
            $club = "";
            $elimination_member = ArcheryEventEliminationMember::find($data->elimination_member_id);
            if ($elimination_member) {
                $participant_member_id = $elimination_member->member_id;

                $detail_member = ArcheryEventParticipantMember::select(
                    'users.name as name',
                    'archery_clubs.name as club_name',
                    'cities.name as city_name',
                    'archery_event_participants.id as participant_id',
                    'archery_event_participants.user_id as user_id',
                    'archery_event_participants.event_id'
                )
                    ->where('archery_event_participant_members.id', $participant_member_id)
                    ->join('archery_event_participants', 'archery_event_participants.id', 'archery_event_participant_members.archery_event_participant_id')
                    ->join('users', "users.id", "=", "archery_event_participants.user_id")
                    ->leftJoin('archery_clubs', 'archery_clubs.id', 'archery_event_participants.club_id')
                    ->leftJoin('cities', 'cities.id', 'archery_event_participants.city_id')
                    ->first();
                $name = $detail_member['name'];
                $rank = $elimination_member->elimination_ranked;
                $club = $detail_member['club_name'] ? $detail_member['club_name'] : "-";
                $city = $detail_member['city_name'] ? $detail_member['city_name'] : "-";
            }


            $result['name_athlete'][] = $name;
            $result['rank'][] = $rank;
            $result['club'][] = $club;
            $result['city'][] = $city;

            $category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);
            if ($category == "") {
                throw new BLoCException("Kategori tidak ditemukan");
            }

            $result['category'][] = $category;
        }

        $mpdf = new Mpdf([
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'bleedMargin' => 0,
            'dpi'        => 110,
            'default_font_size' => 9,
            'shrink_tables_to_fit' => 1.4,
            'tempDir' => public_path() . '/tmp/pdf'
        ]);

        $html = view('template.score_sheet_elimination', [
            "with_contingent" => $with_contingent,
            'peserta1_name' => $result['name_athlete'][0],
            'peserta2_name' => $result['name_athlete'][1],
            'peserta1_club' => $result['club'][0],
            'peserta1_city' => $result['city'][0],
            'peserta2_club' => $result['club'][1],
            'peserta2_city' => $result['city'][1],
            'peserta1_rank' => $result['rank'][0],
            'peserta2_rank' => $result['rank'][1],
            'peserta1_category' => $result['category'][0],
            'peserta2_category' => $result['category'][1],
            "qr" => $base64,
            "event_name" => $event_name,
            "location" => $location_event,
            "elimination_scoring_type" => $elimination->elimination_scoring_type
        ]);

        $mpdf->WriteHTML($html);
        $path = 'asset/score_sheet/';
        $full_path = $path . "score_sheet_elimination.pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return env('APP_HOSTNAME') . $full_path;
    }

    private function getTeam($elimination_id, $round, $match, $category_id, $event_name, $location_event, $with_contingent)
    {
        $elimination = ArcheryEventEliminationGroup::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination tim not found");
        }

        $match_tim = ArcheryEventEliminationGroupMatch::where('elimination_group_id', $elimination_id)
            ->where('round', $round)
            ->where('match', $match)
            ->get();

        if ($match_tim->count() == 0) {
            throw new BLoCException("data not found");
        }

        $string_code = "2-" . $elimination_id . "-" . $match_tim[0]->match . "-" . $match_tim[0]->round . "-t";
        $path = 'asset/score_sheet/' . $category_id  . '/';
        if (!file_exists(public_path() . "/" . $path)) {
            mkdir(public_path() . "/" . $path, 0777);
        }
        $qrCode = new QrCode($string_code);

        $output_qrcode = new Output\Png();

        $qrCode_name_file = "qr_code_" . $string_code . ".png";

        $full_path = $path . $qrCode_name_file;

        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);

        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);

        foreach ($match_tim as $data) {
            $team_name = "";
            $rank = "";
            $club_name = "";
            $city_name = "";
            $array_athlete = [];
            $bud_rest_number = "";

            $elimination_group_tim = ArcheryEventEliminationGroupTeams::find($data->group_team_id);
            if ($elimination_group_tim) {
                $participant_id = $elimination_group_tim->participant_id;
                $participant = ArcheryEventParticipant::find($participant_id);
                if (!$participant) {
                    throw new BLoCException("participant not found");
                }
                $group_member_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $participant_id)->get();
                if ($group_member_team->count() > 0) {
                    foreach ($group_member_team as $key => $value) {
                        $athlete = ArcheryEventParticipantMember::find($value->member_id);
                        if (!$athlete) {
                            throw new BLoCException("athlete not found");
                        }
                        $user = User::find($athlete->user_id);
                        if (!$user) {
                            throw new BLoCException("User not found");
                        }
                        $array_athlete[] = $user->name;
                    }
                }

                $club = ArcheryClub::find($participant->club_id);
                if ($club) {
                    $club_name = $club->name;
                }

                $city = City::find($participant->city_id);
                if ($city) {
                    $city_name = $city->name;
                }

                $team_name = $elimination_group_tim->team_name;
                $rank = $elimination_group_tim->elimination_ranked;

                $bud_rest_number = $data->bud_rest != 0 ? $data->bud_rest . $data->target_face : "";
            }

            $result['name_athlete'][] = $team_name;
            $result['rank'][] = $rank;
            $result['club'][] = $club_name;
            $result['city'][] = $city_name;
            $result["athlete"][] = $array_athlete;
            $result["budrest"][] = $bud_rest_number;

            $category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);
            if ($category == "") {
                throw new BLoCException("Kategori tidak ditemukan");
            }

            $result['category'][] = $category;
        }

        $mpdf = new Mpdf([
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'bleedMargin' => 0,
            'dpi'        => 110,
            'default_font_size' => 9,
            'shrink_tables_to_fit' => 1.4,
            'tempDir' => public_path() . '/tmp/pdf'
        ]);

        $html = view('template.score_sheet_elimination_team', [
            "with_contingent" => $with_contingent,
            'tim_1_name' => $result['name_athlete'][0],
            'tim_2_name' => $result['name_athlete'][1],
            'club_1' => $result['club'][0],
            'city_1' => $result['city'][0],
            'club_2' => $result['club'][1],
            'city_2' => $result['city'][1],
            'tim_1_rank' => $result['rank'][0],
            'tim_2_rank' => $result['rank'][1],
            "athlete_1" => $result["athlete"][0],
            "athlete_2" => $result["athlete"][1],
            "budrest_1" => $result["budrest"][0],
            "budrest_2" => $result["budrest"][1],
            'tim1_category' => $result['category'][0],
            'tim2_category' => $result['category'][1],
            "qr" => $base64,
            "event_name" => $event_name,
            "location" => $location_event,
            "elimination_scoring_type" => $elimination->elimination_scoring_type
        ]);

        $mpdf->WriteHTML($html);
        $path = 'asset/score_sheet/';
        $full_path = $path . "score_sheet_elimination.pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return env('APP_HOSTNAME') . $full_path;
    }
}
