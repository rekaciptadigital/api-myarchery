<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEvent;
use App\Models\ParticipantMemberTeam;
use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class BudRest extends Model
{
    protected $table = 'bud_rest';
    protected $primaryKey = 'id';
    protected $fillable = ['archery_event_category_id', 'bud_rest_start', 'bud_rest_end', 'target_face', 'type'];

    protected function downloadQualificationScoreSheet($category_id,$update_file = false){
        
        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("event category tidak tersedia");
        }
        $path = 'asset/score_sheet/'.$category->id.'/';
        if(!$update_file){
                if (file_exists(public_path()."/".$path."score_sheet_" . $category->id . ".pdf")) {
                return ["url"=>$path."score_sheet_" . $category->id . ".pdf#oldData","member_not_have_budrest"=>[]];
            }
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
            'archery_event_qualification_schedule_full_day.target_face',
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
            ->orderBy("archery_event_participants.club_id","DESC")
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
        if (!file_exists(public_path()."/".$path)) {
            mkdir(public_path()."/".$path, 0777);
        }        
        $member_not_have_budrest = [];
        foreach ($output['data_member'] as $m) {
            if($m["detail_member"]["bud_rest_number"] == 0){
                $member_not_have_budrest[] = $m["detail_member"]["member_id"];
            }
            // return $m;
            $qrCode = new QrCode($m['code']);
            $output_qrcode = new Output\Png();
            // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
            $qrCode_name_file = "qr_code_".$m['code'].".png";
            $full_path = $path . $qrCode_name_file;
            $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
            file_put_contents(public_path().'/'.$full_path, $data_qr_code);

            // return $type;
            $data_get_qr_code = file_get_contents(public_path()."/".$full_path);
            // return $data_get_qr_code;
            $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
            // return $base64;
            $html = \view('template.score_sheet_qualification', [
                "data" => $m,
                "category" => $output['category'],
                "category_label" => $output['category_label'],
                "qr" => $base64,
                "event" => $output['event']
            ]);
            $mpdf->WriteHTML($html);
        }

        $full_path = $path."score_sheet_" . $category->id . ".pdf";
        $mpdf->Output(public_path()."/".$full_path, "F");
        return ["url" => $full_path,"member_not_have_budrest"=>$member_not_have_budrest];
    }
}
