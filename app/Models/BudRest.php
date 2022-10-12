<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEvent;
use App\Models\ParticipantMemberTeam;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Redis;
use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class BudRest extends Model
{
    protected $table = 'bud_rest';
    protected $primaryKey = 'id';
    protected $fillable = ['archery_event_category_id', 'bud_rest_start', 'bud_rest_end', 'target_face', 'type'];

    protected function downloadQualificationScoreSheet($category_id, $update_file = false, $session = 1)
    {

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("event category tidak tersedia");
        }
        $path = 'asset/score_sheet/' . $category->id . '/';
        if (!$update_file) {
            if (file_exists(public_path() . "/" . $path . "score_sheet_" . $category->id . ".pdf")) {
                return ["url" => $path . "score_sheet_" . $category->id . ".pdf#oldData", "member_not_have_budrest" => []];
            }
        }
        $mpdf = new Mpdf([
            'margin_left' => 1,
            'margin_right' => 1,
            'margin_top' => 1,
            'mode' => 'utf-8',
            'format' => 'A4-p',
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
            ->leftJoin('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'participant_member_teams.participant_member_id')
            ->leftJoin('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'participant_member_teams.participant_member_id')
            ->leftJoin('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->leftJoin('users', 'users.id', '=', 'archery_event_participants.user_id')
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_participants.club_id')
            ->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number", "ASC")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face", "ASC")
            ->get();

        // return $output;

        $array_pesrta_baru = [];
        $distance = $category->session_in_qualification <= 2  ? [$category->distance_id, $category->distance_id] : [
            substr($category->distance_id, 0, 2),
            substr($category->distance_id, 2, 2),
            substr($category->distance_id, 4, 2)
        ];
        for ($i = 1; $i <= $category->session_in_qualification; $i++) {
            if ($i == $session) {
                foreach ($participant_member_team as $pmt) {
                    $code_sesi['detail_member'] = $pmt;
                    $code_sesi['sesi'] = $distance[$i - 1] . "-" . $i;
                    $code_sesi['code'] = "1-" . $pmt->member_id . "-" . $i;
                    array_push($array_pesrta_baru, $code_sesi);
                }
            }
        }

        $output['data_member'] = $array_pesrta_baru;
        if (!file_exists(public_path() . "/" . $path)) {
            mkdir(public_path() . "/" . $path, 0777);
        }
        $member_in_budrest = [];
        $member_not_have_budrest = [];
        $i = 0;
        foreach ($output['data_member'] as $m) {
            if ($m["detail_member"]["bud_rest_number"] == 0) {
                $member_not_have_budrest[] = $m["detail_member"]["member_id"];
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i][] = $m;
            if (count($member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i]) >= 2) {
                $i++;
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]['code'] = "1-" . $category->id . "-" . $session . "-" . $m["detail_member"]["bud_rest_number"];
        }

        foreach ($member_in_budrest as $key => $data) {
            if ($key != 0 && count($data["members"]) > 1) {
                $qrCode = new QrCode($data['code']);
                $output_qrcode = new Output\Png();
                // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
                $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                $full_path = $path . $qrCode_name_file;
                $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

                // return $type;
                $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                // return $data_get_qr_code;
                $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                // return $base64;
                $html = \view('template.score_sheet_qualification_group_by_budrest', [
                    "data" => $data["members"],
                    "category" => $output['category'],
                    "category_label" => $output['category_label'],
                    "total_shot_per_stage" => $category->count_shot_in_stage,
                    "total_stage" => $category->count_stage,
                    "qr" => $base64,
                    "event" => $output['event']
                ]);
                $mpdf->WriteHTML($html);
            } else {
                foreach ($data["members"] as $group_member) {
                    foreach ($group_member as $m) {
                        $qrCode = new QrCode($m['code']);
                        $output_qrcode = new Output\Png();
                        // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
                        $qrCode_name_file = "qr_code_" . $m['code'] . ".png";
                        $full_path = $path . $qrCode_name_file;
                        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

                        // return $type;
                        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                        // return $data_get_qr_code;
                        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                        // return $base64;
                        $html = \view('template.score_sheet_qualification', [
                            "data" => $m,
                            "category" => $output['category'],
                            "category_label" => $output['category_label'],
                            "qr" => $base64,
                            "total_shot_per_stage" => $category->count_shot_in_stage,
                            "total_stage" => $category->count_stage,
                            "event" => $output['event']
                        ]);
                        $mpdf->WriteHTML($html);
                    }
                }
            }
        }

        $full_path = $path . "score_sheet_" . $category->id . ".pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return ["url" => $full_path, "member_not_have_budrest" => $member_not_have_budrest];
    }

    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        rrmdir($dir . "/" . $object);
                    else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function setMemberBudrest($category_id)
    {
        $tp = ["A", "C", "B", "D", "E", "F"];
        // ArcheryEventQualificationScheduleFullDay
        $bud_rest = BudRest::where("archery_event_category_id", $category_id)->first();
        if (!$bud_rest) {
            throw new BLoCException("Bud rest belum di set");
        }

        $participants = ArcheryEventParticipant::where("event_category_id", $category_id)->where("status", 1)->get();
        foreach ($participants as $key => $value) {
            $participant_member =  ArcheryEventParticipantMember::where("archery_event_participant_id", $value->id)->first();
            if (!$participant_member) {
                throw new BLoCException("data member tidak ditemukan");
            }

            $q_time = ArcheryEventQualificationTime::where("category_detail_id", $category_id)->first();
            if (!$q_time) {
                throw new BLoCException("jadwal belum ditentukan");
            }

            $jadwal =  ArcheryEventQualificationScheduleFullDay::where("qalification_time_id", $q_time->id)->where("participant_member_id", $participant_member->id)->first();
            if (!$jadwal) {
                ArcheryEventQualificationScheduleFullDay::create([
                    'qalification_time_id' => $q_time->id,
                    'participant_member_id' => $participant_member->id,
                ]);
            }
        }

        $qualification_time = ArcheryEventQualificationTime::where("category_detail_id", $category_id)->get();
        $bud_rest_start = $bud_rest->bud_rest_start;
        $bud_rest_end = $bud_rest->bud_rest_end;

        $target_face = 1;
        $count = 0;
        foreach ($qualification_time as $time) {
            $schedules = ArcheryEventQualificationScheduleFullDay::select("archery_event_qualification_schedule_full_day.*", "archery_event_participants.club_id")
                ->join("archery_event_participant_members", "archery_event_qualification_schedule_full_day.participant_member_id", "=", "archery_event_participant_members.id")
                ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                ->where("qalification_time_id", $time->id)->get()->groupBy("club_id");

            foreach ($schedules as $key => $value) {
                $value["total"] = $value->count();
            }

            $after_sort = $schedules->sortByDesc("total")->values()->all();
            $member = [];

            foreach ($after_sort as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    if ($key2 === "total") {
                        continue;
                    }
                    $member[] = $value2;
                }
            }

            $list_member = [];
            foreach ($member as $a => $value) {
                if ($a === "total") {
                    continue;
                }
                $list_member[] = $value;
            }

            $data_count = count($schedules);
            // $check_budrest = ceil($data_count / $bud_rest->target_face);
            // $check_budrest = $bud_rest_end;
            $data_budrest = [];
            $m_target_face = array_slice($tp, 0, $bud_rest->target_face);
            for ($i = $bud_rest_start; $i <= $bud_rest_end; $i++) {
                $tf = [];
                $tmp_tp = $m_target_face;
                for ($x = 0; $x < $bud_rest->target_face; $x++) {
                    // $tmp_i = rand(0,count($tmp_tp)-1);
                    $tf[] = $tmp_tp[$x];
                    // unset($tmp_tp[$tmp_i]); 
                    // $tmp_tp = array_values($tmp_tp);
                }
                $data_budrest[] = $tf;
            }
            $index = 0;
            for ($z = 0; $z < $bud_rest->target_face; $z++) {
                $brs = $bud_rest_start;
                for ($y = 0; $y < count($data_budrest); $y++) {
                    if (!isset($list_member[$index]))
                        break;
                    ArcheryEventQualificationScheduleFullDay::where("id", $list_member[$index]->id)->update([
                        "bud_rest_number" => $brs,
                        "target_face" => $data_budrest[$y][$z]
                    ]);
                    $count = $count + 1;
                    $brs = $brs + 1;
                    $index++;
                }
            }
        }
        return;
    }

    protected function downloadQualificationSelectionScoreSheet($category_id, $update_file = false, $session = 1)
    {

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("event category tidak tersedia");
        }
        $path = 'asset/score_sheet/' . $category->id . '/';
        if (!$update_file) {
            if (file_exists(public_path() . "/" . $path . "score_sheet_" . $category->id . ".pdf")) {
                return ["url" => $path . "score_sheet_" . $category->id . ".pdf#oldData", "member_not_have_budrest" => []];
            }
        }
        $mpdf = new Mpdf([
            'margin_left' => 1,
            'margin_right' => 1,
            'margin_top' => 1,
            'mode' => 'utf-8',
            'format' => 'A4-p',
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
            ->leftJoin('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'participant_member_teams.participant_member_id')
            ->leftJoin('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'participant_member_teams.participant_member_id')
            ->leftJoin('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->leftJoin('users', 'users.id', '=', 'archery_event_participants.user_id')
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_participants.club_id')
            ->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number", "ASC")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face", "ASC")
            ->get();

        // return $output;

        $array_pesrta_baru = [];
        $distance = $category->session_in_qualification <= 2  ? [$category->distance_id, $category->distance_id] : [
            substr($category->distance_id, 0, 2),
            substr($category->distance_id, 2, 2),
            substr($category->distance_id, 4, 2)
        ];
        for ($i = 1; $i <= $category->session_in_qualification; $i++) {
            if ($i == $session) {
                foreach ($participant_member_team as $pmt) {
                    $code_sesi['detail_member'] = $pmt;
                    $code_sesi['sesi'] = $distance[$i - 1] . "-" . $i;
                    $code_sesi['code'] = "3-" . $pmt->member_id . "-" . $i;
                    array_push($array_pesrta_baru, $code_sesi);
                }
            }
        }

        $output['data_member'] = $array_pesrta_baru;
        if (!file_exists(public_path() . "/" . $path)) {
            mkdir(public_path() . "/" . $path, 0777);
        }
        $member_in_budrest = [];
        $member_not_have_budrest = [];
        $i = 0;
        foreach ($output['data_member'] as $m) {
            if ($m["detail_member"]["bud_rest_number"] == 0) {
                $member_not_have_budrest[] = $m["detail_member"]["member_id"];
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i][] = $m;
            if (count($member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i]) >= 2) {
                $i++;
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]['code'] = "1-" . $category->id . "-" . $session . "-" . $m["detail_member"]["bud_rest_number"];
        }

        foreach ($member_in_budrest as $key => $data) {
            if ($key != 0 && count($data["members"]) > 1) {
                $qrCode = new QrCode($data['code']);
                $output_qrcode = new Output\Png();
                // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
                $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                $full_path = $path . $qrCode_name_file;
                $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

                // return $type;
                $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                // return $data_get_qr_code;
                $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                // return $base64;
                $html = \view('template.event_selection.score_sheet_qualification_group_by_budrest', [
                    "data" => $data["members"],
                    "category" => $output['category'],
                    "category_label" => $output['category_label'],
                    "total_shot_per_stage" => $category->count_shot_in_stage,
                    "total_stage" => $category->count_stage,
                    "qr" => $base64,
                    "event" => $output['event']
                ]);
                $mpdf->WriteHTML($html);
            } else {
                foreach ($data["members"] as $group_member) {
                    foreach ($group_member as $m) {
                        $qrCode = new QrCode($m['code']);
                        $output_qrcode = new Output\Png();
                        // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
                        $qrCode_name_file = "qr_code_" . $m['code'] . ".png";
                        $full_path = $path . $qrCode_name_file;
                        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

                        // return $type;
                        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                        // return $data_get_qr_code;
                        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                        // return $base64;
                        $html = \view('template.event_selection.score_sheet_qualification', [
                            "data" => $m,
                            "category" => $output['category'],
                            "category_label" => $output['category_label'],
                            "qr" => $base64,
                            "total_shot_per_stage" => $category->count_shot_in_stage,
                            "total_stage" => $category->count_stage,
                            "event" => $output['event']
                        ]);
                        $mpdf->WriteHTML($html);
                    }
                }
            }
        }

        $full_path = $path . "score_sheet_qualification_selection" . $category->id . ".pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return ["url" => $full_path, "member_not_have_budrest" => $member_not_have_budrest];
    }

    protected function downloadEliminationSelectionScoreSheet($category_id, $update_file = false, $session = 1)
    {

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("event category tidak tersedia");
        }
        $path = 'asset/score_sheet/' . $category->id . '/';
        if (!$update_file) {
            if (file_exists(public_path() . "/" . $path . "score_sheet_" . $category->id . ".pdf")) {
                return ["url" => $path . "score_sheet_" . $category->id . ".pdf#oldData", "member_not_have_budrest" => []];
            }
        }
        $mpdf = new Mpdf([
            'margin_left' => 1,
            'margin_right' => 1,
            'margin_top' => 1,
            'mode' => 'utf-8',
            'format' => 'A4-p',
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
            ->leftJoin('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'participant_member_teams.participant_member_id')
            ->leftJoin('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'participant_member_teams.participant_member_id')
            ->leftJoin('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->leftJoin('users', 'users.id', '=', 'archery_event_participants.user_id')
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_participants.club_id')
            ->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number", "ASC")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face", "ASC")
            ->get();

        // return $output;

        $array_pesrta_baru = [];
        $distance = env('COUNT_STAGE_ELIMINATION_SELECTION') <= 2  ? [$category->distance_id, $category->distance_id] : [
            substr($category->distance_id, 0, 2),
            substr($category->distance_id, 2, 2),
            substr($category->distance_id, 4, 2)
        ];
        for ($i = 1; $i <= env('COUNT_STAGE_ELIMINATION_SELECTION'); $i++) {
            if ($i == $session) {
                foreach ($participant_member_team as $pmt) {
                    $code_sesi['detail_member'] = $pmt;
                    // $code_sesi['sesi'] = $distance[$i - 1] . "-" . $i;
                    $code_sesi['sesi'] = '';
                    $code_sesi['code'] = "4-" . $pmt->member_id . "-" . $i;
                    array_push($array_pesrta_baru, $code_sesi);
                }
            }
        }

        $output['data_member'] = $array_pesrta_baru;
        if (!file_exists(public_path() . "/" . $path)) {
            mkdir(public_path() . "/" . $path, 0777);
        }
        $member_in_budrest = [];
        $member_not_have_budrest = [];
        $i = 0;
        foreach ($output['data_member'] as $m) {
            if ($m["detail_member"]["bud_rest_number"] == 0) {
                $member_not_have_budrest[] = $m["detail_member"]["member_id"];
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i][] = $m;
            if (count($member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i]) >= 2) {
                $i++;
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]['code'] = "4-" . $category->id . "-" . $session . "-" . $m["detail_member"]["bud_rest_number"];
        }

        foreach ($member_in_budrest as $key => $data) {
            if ($key != 0 && count($data["members"]) > 1) {
                $qrCode = new QrCode($data['code']);
                $output_qrcode = new Output\Png();
                // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
                $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                $full_path = $path . $qrCode_name_file;
                $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

                // return $type;
                $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                // return $data_get_qr_code;
                $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                // return $base64;
                $html = \view('template.event_selection.score_sheet_elimination_group_by_budrest', [
                    "data" => $data["members"],
                    "category" => $output['category'],
                    "category_label" => $output['category_label'],
                    "total_shot_per_stage" => env('COUNT_SHOT_IN_STAGE_ELIMINATION_SELECTION'),
                    "total_stage" => env('COUNT_STAGE_ELIMINATION_SELECTION'),
                    "qr" => $base64,
                    "event" => $output['event']
                ]);
                $mpdf->WriteHTML($html);
            }
            //  else {
            //     foreach ($data["members"] as $group_member) {
            //         foreach ($group_member as $m) {
            //             $qrCode = new QrCode($m['code']);
            //             $output_qrcode = new Output\Png();
            //             // $qrCode_name_file = "qr_code_" . $pmt->member_id . ".png";
            //             $qrCode_name_file = "qr_code_" . $m['code'] . ".png";
            //             $full_path = $path . $qrCode_name_file;
            //             $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
            //             file_put_contents(public_path() . '/' . $full_path, $data_qr_code);

            //             // return $type;
            //             $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
            //             // return $data_get_qr_code;
            //             $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
            //             // return $base64;
            //             $html = \view('template.event_selection.score_sheet_elimination', [
            //                 "data" => $m,
            //                 "category" => $output['category'],
            //                 "category_label" => $output['category_label'],
            //                 "qr" => $base64,
            //                 "total_shot_per_stage" => env('COUNT_SHOT_IN_STAGE_ELIMINATION_SELECTION'),
            //                 "total_stage" => env('COUNT_STAGE_ELIMINATION_SELECTION'),
            //                 "event" => $output['event']
            //             ]);
            //             $mpdf->WriteHTML($html);
            //         }
            //     }
            // }
        }

        $full_path = $path . "score_sheet_elimination_selection" . $category->id . ".pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return ["url" => $full_path, "member_not_have_budrest" => $member_not_have_budrest];
    }
}
