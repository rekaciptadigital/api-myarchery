<?php

namespace App\BLoC\Web\EliminationScoreSheet;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventCertificateTemplates;
use App\Models\ArcheryEventElimination;
use App\Models\BudRest;
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
        $event_elimination_id = $parameters->get('event_elimination_id');
        $round = $parameters->get('round');
        $match = $parameters->get('match');

        $data_member = ArcheryEventEliminationMatch::where('event_elimination_id', $event_elimination_id)
            ->where('round', $round)
            ->where('match', $match)
            ->get();

        if ($data_member->count() == 0) {
            throw new BLoCException("data not found");
        }

        $elimination = ArcheryEventElimination::find($data_member[0]->event_elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination not found");
        }

        $string_code = "2-" . $data_member[0]->event_elimination_id . "-" . $data_member[0]->match . "-" . $data_member[0]->round;
        $path = 'asset/score_sheet/' . $elimination->event_category_id  . '/';
        $qrCode = new QrCode($string_code);
        $output_qrcode = new Output\Png();
        // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
        $qrCode_name_file = "qr_code_" . $$string_code . ".png";
        $full_path = $path . $qrCode_name_file;
        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

        // return $type;
        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
        // return $data_get_qr_code;
        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);

        foreach ($data_member as $data) {

            $elimination_matches_id = $data->id;
            $elimination_member = ArcheryEventEliminationMember::find($data->elimination_member_id);
            if (!$elimination_member) {
                throw new BLoCException("data not found");
            }
            $participant_member_id = $elimination_member->member_id;

            // $scoring = ArcheryScoring::where('participant_member_id', $participant_member_id)
            //     ->where('item_value', 'archery_event_elimination_matches')
            //     ->where('item_id', $elimination_matches_id)
            //     ->first();

            // if (!$scoring) {
            //     throw new BLoCException("data scooring not found");
            // }

            $detail_member = ArcheryEventParticipantMember::select(
                'archery_event_participant_members.name as name',
                'archery_clubs.name as club_name',
                'archery_event_participants.id as participant_id',
                'archery_event_participants.user_id as user_id'
            )
                ->where('archery_event_participant_members.id', $participant_member_id)
                ->leftJoin('archery_event_participants', 'archery_event_participants.id', 'archery_event_participant_members.archery_event_participant_id')
                ->leftJoin('archery_clubs', 'archery_clubs.id', 'archery_event_participants.club_id')
                ->first();

            $result['name_athlete'][] = $detail_member['name'];
            $result['rank'][] = $elimination_member->elimination_ranked;
            $result['club'][] = $detail_member['club_name'] ? $detail_member['club_name'] : "-";

            $category = ArcheryEventCertificateTemplates::getCategoryLabel($detail_member['participant_id'], $detail_member['user_id']);
            if ($category == "") throw new BLoCException("Kategori tidak ditemukan");

            $result['category'][] = $category;
            // $score[] = (array)json_decode($scoring->scoring_detail);
        }

        // $scoring = json_decode(json_encode($score), true);



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
            'peserta1_name' => $result['name_athlete'][0], 'peserta2_name' => $result['name_athlete'][1],
            'peserta1_club' => $result['club'][0], 'peserta2_club' => $result['club'][1],
            'peserta1_rank' => $result['rank'][0], 'peserta2_rank' => $result['rank'][1],
            'peserta1_category' => $result['category'][0], 'peserta2_category' => $result['category'][1],
            // 'score1' => $scoring[0]['shot'],
            // 'score2' => $scoring[1]['shot'],
            "qr" => $base64
        ]);
        $mpdf->WriteHTML($html);
        $path = 'asset/score_sheet/';
        $full_path = $path . "score_sheet_elimination.pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return env('APP_HOSTNAME') . $full_path;
    }

    protected function validation($parameters)
    {
        return [
            'event_elimination_id' => 'required',
            'round' => 'required',
            'match' => 'required',
        ];
    }
}
