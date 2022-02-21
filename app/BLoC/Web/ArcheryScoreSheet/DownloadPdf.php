<?php

namespace App\BLoC\Web\ArcheryScoreSheet;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ParticipantMemberTeam;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class DownloadPdf extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_id = $parameters->get('event_category_id');
        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("event category tidak tersedia");
        }
        $mpdf = new Mpdf([
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'mode' => 'utf-8',
            'format' => 'A6-P',
            'orientation' => 'P',
            'bleedMargin' => 0,
            'dpi'        => 110,
            'default_font_size' => 7,
            'shrink_tables_to_fit' => 1.4,
            'tempDir' => public_path() . '/tmp/pdf'
        ]);
        $label = ArcheryEventCategoryDetail::getCategoryLabelComplete($category->id);
        $event = ArcheryEvent::find($category->event_id);
        $output = [
            'event' => $event,
            'category_label' => $label,
            'category' => $category
        ];

        $participant_member_team = ParticipantMemberTeam::select(
            'participant_member_teams.participant_member_id as member_id',
            'archery_event_qualification_schedule_full_day.bud_rest_number',
            'archery_event_participants.id as participant_id',
            'users.name',
            'archery_clubs.name as club_name'
        )
            ->where('participant_member_teams.event_category_id', $category->id)
            ->join('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'participant_member_teams.participant_member_id')
            ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'participant_member_teams.participant_member_id')
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('users', 'users.id', '=', 'archery_event_participants.user_id')
            ->join('archery_clubs', 'archery_clubs.id', '=', 'archery_event_participants.club_id')
            ->get();

        // return $output;

        $array_pesrta_baru = [];
        foreach ($participant_member_team as $pmt) {
            for ($i = 1; $i <= $category->session_in_qualification; $i++) {
                $code_sesi['detail_member'] = $pmt;
                $code_sesi['sesi'] = $i;
                $code_sesi['code'] = "1-" . $pmt->member_id . "-" . $i;
                array_push($array_pesrta_baru, $code_sesi);
            }
        }

        $output['data_member'] = $array_pesrta_baru;

        foreach ($output['data_member'] as $m) {
            // return $m;
            $qrCode = new QrCode($m['code']);
            $output_qrcode = new Output\Png();
            // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
            $qrCode_name_file = "qr_code.png";
            $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
            file_put_contents('./asset/' . $qrCode_name_file, $data_qr_code);

            $path = 'asset/qr_code.png';
            // return $type;
            $data_get_qr_code = file_get_contents($path);
            // return $data_get_qr_code;
            $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
            // return $base64;
            $html = \view('template.invoice', [
                "data" => $m,
                "category" => $output['category'],
                "category_label" => $output['category_label'],
                "qr" => $base64,
                "event" => $output['event']
            ]);
            $mpdf->WriteHTML($html);
        }
        $mpdf->Output("./asset/score_sheet/score_sheet_" . $category->id . ".pdf", "F");
        return env('APP_HOSTNAME') . "asset/score_sheet/score_sheet_" . $category->id . ".pdf";
    }

    protected function validation($parameters)
    {
        return [
            "event_category_id" => 'required|integer'
        ];
    }
}
