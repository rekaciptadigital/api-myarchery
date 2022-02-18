<?php

namespace App\BLoC\App\ArcheryScoreSheet;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ParticipantMemberTeam;
use DAI\Utils\Abstracts\Retrieval;
use Mpdf\Mpdf;

class DownloadPdf extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // return "ok";
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
        $category = ArcheryEventCategoryDetail::find(8);
        $label = ArcheryEventCategoryDetail::getCategoryLabelComplete(8);
        $event = ArcheryEvent::find($category->event_id);
        // return $label;
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
            ->where('participant_member_teams.event_category_id', 8)
            ->join('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'participant_member_teams.participant_member_id')
            ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'participant_member_teams.participant_member_id')
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('users', 'users.id', '=', 'archery_event_participants.user_id')
            ->join('archery_clubs', 'archery_clubs.id', '=', 'archery_event_participants.club_id')
            ->get();

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

        // return $output;

        foreach ($output['data_member'] as $m) {
            $html = \view('template.invoice', [
                "data" => $m,
                "category" => $output['category']
            ]);
            $mpdf->WriteHTML($html);
        }
        $mpdf->Output('./asset/naruto.pdf', 'F');
    }

    protected function validation($parameters)
    {
        return [];
    }
}
