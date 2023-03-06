<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEvent;
use App\Models\ParticipantMemberTeam;
use DAI\Utils\Exceptions\BLoCException;
use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class BudRest extends Model
{
    protected $table = 'bud_rest';
    protected $primaryKey = 'id';
    protected $fillable = ['archery_event_category_id', 'bud_rest_start', 'bud_rest_end', 'target_face', 'type'];

    protected function downloadQualificationScoreSheet($category_id, $update_file = true, $session = 1)
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

        $participant_member_team = ArcheryEventParticipantMember::select(
            'archery_event_participant_members.id as member_id',
            'archery_event_qualification_schedule_full_day.bud_rest_number',
            'archery_event_qualification_schedule_full_day.target_face',
            'archery_event_participants.id as participant_id',
            'users.name',
            'archery_clubs.name as club_name',
            'cities.name as city_name'
        )
            ->leftJoin('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'archery_event_participant_members.id')
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('users', 'users.id', '=', 'archery_event_participants.user_id')
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_participants.club_id')
            ->leftJoin('cities', 'cities.id', '=', 'archery_event_participants.city_id')
            ->where('archery_event_participants.event_category_id', $category->id)
            ->where('archery_event_participants.status', 1)
            ->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number", "ASC")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face", "ASC")
            ->get();

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
        $i = 1;
        foreach ($output['data_member'] as $m) {
            if ($m["detail_member"]["bud_rest_number"] == 0) {
                throw new BLoCException("masih ada peserta yang belum memiliki bantalan");
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i][] = $m;
            if (count($member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i]) >= 2) {
                $i++;
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]['code'] = "1-" . $category->id . "-" . $session . "-" . $m["detail_member"]["bud_rest_number"];
        }

        foreach ($member_in_budrest as $key => $data) {
            if (isset($data["members"]) && count($data["members"]) == 1) {
                foreach ($data["members"] as $dm_key => $dm) {
                    if (count($dm) == 1) {
                        $qrCode = new QrCode($data['code']);
                        $output_qrcode = new Output\Png();
                        $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                        $full_path = $path . $qrCode_name_file;
                        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);
                        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                        $html = \view('template.score_sheet_qualification', [
                            "with_contingent" => $event->with_contingent,
                            "data" => $dm[0],
                            "category" => $output['category'],
                            "category_label" => $output['category_label'],
                            "qr" => $base64,
                            "total_shot_per_stage" => $category->count_shot_in_stage,
                            "total_stage" => $category->count_stage,
                            "event" => $output['event'],
                            "row_height" => "45px"
                        ]);
                        $mpdf->AddPage("P");
                        $mpdf->WriteHTML($html);
                    } else {
                        $qrCode = new QrCode($data['code']);
                        $output_qrcode = new Output\Png();
                        $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                        $full_path = $path . $qrCode_name_file;
                        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);
                        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                        $html = \view('template.score_sheet_qualification_group_by_budrest', [
                            "with_contingent" => $event->with_contingent,
                            "data" => $data["members"],
                            "category" => $output['category'],
                            "category_label" => $output['category_label'],
                            "total_shot_per_stage" => $category->count_shot_in_stage,
                            "total_stage" => $category->count_stage,
                            "qr" => $base64,
                            "event" => $output['event'],
                            "row_height" => "35px"
                        ]);
                        $mpdf->AddPage("L");
                        $mpdf->WriteHTML($html);
                    }
                }
            } else {
                $qrCode = new QrCode($data['code']);
                $output_qrcode = new Output\Png();
                $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                $full_path = $path . $qrCode_name_file;
                $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                file_put_contents(public_path() . '/' . $full_path, $data_qr_code);
                $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                $html = \view('template.score_sheet_qualification_group_by_budrest', [
                    "with_contingent" => $event->with_contingent,
                    "data" => $data["members"],
                    "category" => $output['category'],
                    "category_label" => $output['category_label'],
                    "total_shot_per_stage" => $category->count_shot_in_stage,
                    "total_stage" => $category->count_stage,
                    "qr" => $base64,
                    "event" => $output['event'],
                    "row_height" => "40px",
                ]);
                $mpdf->AddPage("P");
                $mpdf->WriteHTML($html);
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

    public static function setMemberBudrest($category_id, $with_contingent)
    {

        $tf = ["A", "C", "B", "D", "E", "F"];
        $bud_rest = BudRest::where("archery_event_category_id", $category_id)->first();
        if (!$bud_rest) {
            throw new BLoCException("Bud rest belum di set");
        }

        $participants = ArcheryEventParticipant::where("event_category_id", $category_id)
            ->where("status", 1)
            ->get();
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

        $qualification_time = ArcheryEventQualificationTime::where("category_detail_id", $category_id)->first();
        if (!$qualification_time) {
            throw new BLoCException("jadwal belum dibuat");
        }
        $list_schedule_full_day = ArcheryEventQualificationScheduleFullDay::where("qalification_time_id", $qualification_time->id)->get();

        foreach ($list_schedule_full_day as $key => $l_s_f_d) {
            $l_s_f_d->bud_rest_number = 0;
            $l_s_f_d->target_face = "";
            $l_s_f_d->save();
        }

        $bud_rest_start = $bud_rest->bud_rest_start;
        $bud_rest_end = $bud_rest->bud_rest_end;

        if ($with_contingent == 1) {
            $tag = "city_id";
        } else {
            $tag = "club_id";
        }

        $data = ArcheryEventParticipant::select($with_contingent == 0 ? "archery_event_participants.club_id" : "archery_event_participants.city_id");

        if ($with_contingent == 1) {
            $data = $data->join("cities", "cities.id", "=", "archery_event_participants.city_id");
        } else {
            $data = $data->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
        }

        $data = $data->where("archery_event_participants.status", 1)
            ->where("archery_event_participants.event_category_id", $category_id)
            ->distinct()
            ->get();

        $club_or_city_ids = [];
        foreach ($data as $key => $d) {
            $club_or_city_ids[$d[$tag]] = [];
            $club_or_city_ids[$d[$tag]][$tag] = $d[$tag];
            $club_or_city_ids[$d[$tag]]["total"] = 0;
            // $club_or_city_ids[$d[$tag]]["city_name"] = "";
        }

        $schedules = ArcheryEventQualificationScheduleFullDay::select(
            "archery_event_qualification_schedule_full_day.*",
            // "cities.name as city_name",
            $with_contingent == 0 ? "archery_event_participants.club_id" : "archery_event_participants.city_id"
        )
            ->join("archery_event_participant_members", "archery_event_qualification_schedule_full_day.participant_member_id", "=", "archery_event_participant_members.id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            // ->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id")
            ->where("qalification_time_id", $qualification_time->id)
            ->get();



        foreach ($schedules as $key => $value) {
            $club_or_city_ids[$value[$tag]]["total"] += 1;
            // $club_or_city_ids[$value[$tag]]["city_name"] = $value->city_name;
        }

        usort($club_or_city_ids, function ($a, $b) {
            return $b["total"] > $a["total"] ? 1 : -1;
        });


        foreach ($club_or_city_ids as $key1 => $value1) { // 3275
            for ($i = 0; $i < $bud_rest->target_face; $i++) { // A
                $total_target_face = ArcheryEventQualificationScheduleFullDay::where("target_face", $tf[$i])
                    ->where("qalification_time_id", $qualification_time->id)
                    ->get()
                    ->count();
                $total_budrest = $bud_rest_end - $bud_rest_start + 1; // 18
                if ($value1["total"] <= $total_budrest - $total_target_face) {
                    for ($j = $bud_rest_start; $j <= $bud_rest_end; $j++) { // 1
                        $check = ArcheryEventQualificationScheduleFullDay::where("bud_rest_number", $j)
                            ->where("target_face", $tf[$i])
                            ->where("qalification_time_id", $qualification_time->id)
                            ->first();
                        if ($check) {
                            continue;
                        }

                        foreach ($schedules as $key2 => $value2) {
                            if ($value1[$tag] == $value2[$tag]) {
                                $value2->bud_rest_number = $j;
                                $value2->target_face = $tf[$i];
                                $value2->save();
                                unset($schedules[$key2]);
                                break;
                            }
                        }
                    }
                    break;
                }
                continue;
            }
        }

        $list_member = [];
        foreach ($club_or_city_ids as $coc_key => $coc_ids) {
            foreach ($schedules as $key => $s) {
                if ($coc_ids[$tag] == $s[$tag]) {
                    $list_member[] = $s;
                }
            }
        }

        for ($i = 0; $i < $bud_rest->target_face; $i++) {
            for ($j = $bud_rest_start; $j <= $bud_rest_end; $j++) {
                foreach ($list_member as $key_lm => $value_lm) {
                    $check = ArcheryEventQualificationScheduleFullDay::where("bud_rest_number", $j)
                        ->where("target_face", $tf[$i])
                        ->where("qalification_time_id", $qualification_time->id)
                        ->first();
                    if ($check) {
                        break;
                    }
                    $jadwal_member = ArcheryEventQualificationScheduleFullDay::find($value_lm["id"]);
                    $jadwal_member->bud_rest_number = $j;
                    $jadwal_member->target_face = $tf[$i];
                    $jadwal_member->save();
                    unset($list_member[$key_lm]);
                }
            }
        }


        return true;
    }

    protected function downloadQualificationSelectionScoreSheet($category_id, $update_file = true, $session = 1)
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

        $participant_member_team = ArcheryEventParticipantMember::select(
            'archery_event_participant_members.id as member_id',
            'archery_event_qualification_schedule_full_day.bud_rest_number',
            'archery_event_qualification_schedule_full_day.target_face',
            'archery_event_participants.id as participant_id',
            'users.name',
            'archery_clubs.name as club_name',
            'cities.name as city_name'
        )
            ->leftJoin('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'archery_event_participant_members.id')
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('users', 'users.id', '=', 'archery_event_participants.user_id')
            ->leftJoin('archery_clubs', 'archery_clubs.id', '=', 'archery_event_participants.club_id')
            ->leftJoin('cities', 'cities.id', '=', 'archery_event_participants.city_id')
            ->where('archery_event_participants.event_category_id', $category->id)
            ->where('archery_event_participants.status', 1)
            ->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number", "ASC")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face", "ASC")
            ->get();

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
        $i = 1;
        foreach ($output['data_member'] as $m) {
            if ($m["detail_member"]["bud_rest_number"] == 0) {
                throw new BLoCException("masih ada peserta yang belum memiliki bantalan");
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i][] = $m;
            if (count($member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i]) >= 2) {
                $i++;
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]['code'] = "3-" . $category->id . "-" . $session . "-" . $m["detail_member"]["bud_rest_number"];
        }

        foreach ($member_in_budrest as $key => $data) {
            if (isset($data["members"]) && count($data["members"]) == 1) {
                foreach ($data["members"] as $dm_key => $dm) {
                    if (count($dm) == 1) {
                        $qrCode = new QrCode($data['code']);
                        $output_qrcode = new Output\Png();
                        $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                        $full_path = $path . $qrCode_name_file;
                        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);
                        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                        $html = \view('template.score_sheet_qualification', [
                            "with_contingent" => $event->with_contingent,
                            "data" => $dm[0],
                            "category" => $output['category'],
                            "category_label" => $output['category_label'],
                            "qr" => $base64,
                            "total_shot_per_stage" => $category->count_shot_in_stage,
                            "total_stage" => $category->count_stage,
                            "event" => $output['event'],
                            "row_height" => "45px"
                        ]);
                        $mpdf->AddPage("P");
                        $mpdf->WriteHTML($html);
                    } else {
                        $qrCode = new QrCode($data['code']);
                        $output_qrcode = new Output\Png();
                        $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                        $full_path = $path . $qrCode_name_file;
                        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);
                        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                        $html = \view('template.score_sheet_qualification_group_by_budrest', [
                            "with_contingent" => $event->with_contingent,
                            "data" => $data["members"],
                            "category" => $output['category'],
                            "category_label" => $output['category_label'],
                            "total_shot_per_stage" => $category->count_shot_in_stage,
                            "total_stage" => $category->count_stage,
                            "qr" => $base64,
                            "event" => $output['event'],
                            "row_height" => "35px"
                        ]);
                        $mpdf->AddPage("L");
                        $mpdf->WriteHTML($html);
                    }
                }
            } else {
                $qrCode = new QrCode($data['code']);
                $output_qrcode = new Output\Png();
                $qrCode_name_file = "qr_code_" . $data['code'] . ".png";
                $full_path = $path . $qrCode_name_file;
                $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
                file_put_contents(public_path() . '/' . $full_path, $data_qr_code);
                $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
                $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);
                $html = \view('template.score_sheet_qualification_group_by_budrest', [
                    "with_contingent" => $event->with_contingent,
                    "data" => $data["members"],
                    "category" => $output['category'],
                    "category_label" => $output['category_label'],
                    "total_shot_per_stage" => $category->count_shot_in_stage,
                    "total_stage" => $category->count_stage,
                    "qr" => $base64,
                    "event" => $output['event'],
                    "row_height" => "40px",
                ]);
                $mpdf->AddPage("P");
                $mpdf->WriteHTML($html);
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
                    "event" => $output['event'],
                    "elimination_scoring_type" => "all"
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
