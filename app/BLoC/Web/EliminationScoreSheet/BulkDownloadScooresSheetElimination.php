<?php

namespace App\BLoC\Web\EliminationScoreSheet;

use App\Libraries\PdfLibrary;
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
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class BulkDownloadScooresSheetElimination extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $elimination_id = $parameters->get('event_elimination_id');
        $round = $parameters->get('round');
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

        $path = 'asset/score_sheet/' . $category_id  . '/';
        if (!file_exists(public_path() . "/" . $path)) {
            mkdir(public_path() . "/" . $path, 0777);
        }

        $file_name = $path . "scoore_sheet_elimination_" . $elimination_id . "_" . $round;

        if (strtolower($category->type) == "team") {
            return $this->getTeam($elimination_id, $round, $category_id, $event_name, $location_event, $mpdf, $path, $file_name);
        } else {
            return $this->getMember($elimination_id, $round, $category_id, $event_name, $location_event, $mpdf, $path, $file_name);
        }
    }

    protected function validation($parameters)
    {
        return [
            'event_elimination_id' => 'required',
            'round' => 'required',
            'category_id' => 'required'
        ];
    }

    private function getMember($elimination_id, $round, $category_id, $event_name, $location_event, $mpdf, $path, $file_name)
    {
        $elimination = ArcheryEventElimination::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination not found");
        }

        $list_match = [];
        $data_member = ArcheryEventEliminationMatch::where('event_elimination_id', $elimination_id)
            ->where('round', $round)
            ->get();

        if ($data_member->count() == 0) {
            throw new BLoCException("data not found");
        }

        foreach ($data_member as $dm) {
            $list_match[$dm->match][$dm->index] = $dm;
        }

        $all = [];
        foreach ($list_match as $value) {
            $string_code = "2-" . $value[0]->event_elimination_id . "-" . $value[0]->match . "-" . $value[0]->round;
            $qrCode = new QrCode($string_code);

            $output_qrcode = new Output\Png();

            $qrCode_name_file = "qr_code_" . $string_code . ".png";

            $full_path = $path . $qrCode_name_file;

            $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);

            file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

            $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
            $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);

            $result = [];
            foreach ($value as $v) {
                $name = "";
                $rank = "";
                $club = "";
                $elimination_member = ArcheryEventEliminationMember::find($v->elimination_member_id);
                if ($elimination_member) {
                    $participant_member_id = $elimination_member->member_id;

                    $detail_member = ArcheryEventParticipantMember::select(
                        'archery_event_participant_members.name as name',
                        'archery_clubs.name as club_name',
                        'archery_event_participants.id as participant_id',
                        'archery_event_participants.user_id as user_id',
                        'archery_event_participants.event_id'
                    )
                        ->where('archery_event_participant_members.id', $participant_member_id)
                        ->leftJoin('archery_event_participants', 'archery_event_participants.id', 'archery_event_participant_members.archery_event_participant_id')
                        ->leftJoin('archery_clubs', 'archery_clubs.id', 'archery_event_participants.club_id')
                        ->first();
                    $name = $detail_member['name'];
                    $rank = $elimination_member->elimination_ranked;
                    $club = $detail_member['club_name'] ? $detail_member['club_name'] : "-";
                }


                $result['name_athlete'][] = $name;
                $result['rank'][] = $rank;
                $result['club'][] = $club;

                $category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);
                if ($category == "") {
                    throw new BLoCException("Kategori tidak ditemukan");
                }

                $result['category'][] = $category;
            }

            $html = view('template.score_sheet_elimination', [
                'peserta1_name' => $result['name_athlete'][0],
                'peserta2_name' => $result['name_athlete'][1],
                'peserta1_club' => $result['club'][0],
                'peserta2_club' => $result['club'][1],
                'peserta1_rank' => $result['rank'][0],
                'peserta2_rank' => $result['rank'][1],
                'peserta1_category' => $result['category'][0],
                'peserta2_category' => $result['category'][1],
                "qr" => $base64,
                "event_name" => $event_name,
                "location" => $location_event
            ]);

            $all[] = $html;
        }

        PdfLibrary::setArrayDoc($all)->setFileName($file_name)->savePdf($mpdf, null, null);
        return [
            "file_name" => env('APP_HOSTNAME') . $file_name,
        ];
    }

    private function getTeam($elimination_id, $round, $category_id, $event_name, $location_event, $mpdf, $path, $file_name)
    {
        $elimination = ArcheryEventEliminationGroup::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination tim not found");
        }

        $match_tim = ArcheryEventEliminationGroupMatch::where('elimination_group_id', $elimination_id)
            ->where('round', $round)
            ->get();

        if ($match_tim->count() == 0) {
            throw new BLoCException("data not found");
        }


        $list_match = [];
        foreach ($match_tim as $mt) {
            $list_match[$mt->match][$mt->index] = $mt;
        }


        $all = [];

        foreach ($list_match as $value) {
            $string_code = "2-" . $elimination_id . "-" . $value[0]->match . "-" . $value[0]->round . "-t";

            $qrCode = new QrCode($string_code);

            $output_qrcode = new Output\Png();

            $qrCode_name_file = "qr_code_" . $string_code . ".png";

            $full_path = $path . $qrCode_name_file;

            $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);

            file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

            $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
            $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
            $result = [];

            foreach ($value as $data) {
                $team_name = "";
                $rank = "";
                $club_name = "";
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
                    if (!$club) {
                        throw new BLoCException("club not found");
                    }

                    $team_name = $elimination_group_tim->team_name;
                    $rank = $elimination_group_tim->elimination_ranked;
                    $club_name = $club->name;
                    $bud_rest_number = $data->bud_rest != 0 ? $data->bud_rest . $data->target_face : "";
                }

                $result['name_athlete'][] = $team_name;
                $result['rank'][] = $rank;
                $result['club'][] = $club_name;
                $result["athlete"][] = $array_athlete;
                $result["budrest"][] = $bud_rest_number;

                $category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);
                if ($category == "") {
                    throw new BLoCException("Kategori tidak ditemukan");
                }

                $result['category'][] = $category;
            }

            $html = view('template.score_sheet_elimination_team', [
                'tim_1_name' => $result['name_athlete'][0],
                'tim_2_name' => $result['name_athlete'][1],
                'club_1' => $result['club'][0],
                'club_2' => $result['club'][1],
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
                "location" => $location_event
            ]);

            $all[] = $html;
        }

        PdfLibrary::setArrayDoc($all)->setFileName($file_name)->savePdf($mpdf, null, null);
        return [
            "file_name" => env('APP_HOSTNAME') . $file_name,
        ];
    }
}
