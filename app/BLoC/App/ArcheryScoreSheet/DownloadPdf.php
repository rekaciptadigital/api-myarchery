<?php

namespace App\BLoC\App\ArcheryScoreSheet;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationScheduleFullDay;
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
        $mpdf = new Mpdf();
        $category = ArcheryEventCategoryDetail::find(8);
        $label = ArcheryEventCategoryDetail::getCategoryLabelComplete(8);
        $event = ArcheryEvent::find($category->event_id);
        // return $label;
        $output = [
            'event' => $event,
            'category_label' => $label
        ];
        // return $output;
        $data = [];
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

        if ($participant_member_team->count() > 0) {
        }

        foreach ($participant_member_team as $pmt) {
            for ($i = 1; $i <= $category->session_in_qualification; $i++) {
                $pmt['sesi'] = $i;
                $pmt['code'] = "1-" . $pmt->member_id . "-" . $i;
                $data[] = $pmt;
            }
        }

        $output['member'] = $data;
        return $output;
        $html = \view('template.invoice', [
            // "data" => $data
        ]);
        for ($i = 0; $i < 2; $i++) {
            $mpdf->AddPage();
            $mpdf->WriteHTML($html);
        }

        // Output a PDF file directly to the browser
        $mpdf->Output('./asset/naruto.pdf', 'F');
    }

    protected function validation($parameters)
    {
        return [];
    }
}
