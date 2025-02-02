<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEvent;
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
        $category = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            "archery_events.is_private",
            "archery_events.parent_classification",
            "archery_events.classification_country_id"
        )
            ->join("archery_events", "archery_events.id", "=", "archery_event_category_details.event_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();

        $parent_classifification_id = $category->parent_classification;

        if ($parent_classifification_id == 0) {
            throw new BLoCException("parent calassification_id invalid");
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
            "archery_event_participants.club_id",
            "archery_clubs.name as club_name",
            "archery_event_participants.city_id",
            $category->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
            "archery_event_participants.classification_country_id",
            "countries.name as country_name",
            "archery_event_participants.classification_province_id",
            $category->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
            "archery_event_participants.children_classification_id",
            "children_classification_members.title as children_classification_members_name"
        )
            ->leftJoin('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'archery_event_participant_members.id')
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('users', 'users.id', '=', 'archery_event_participants.user_id');

        // jika mewakili club
        $participant_member_team = $participant_member_team->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");


        // jika mewakili negara
        $participant_member_team = $participant_member_team->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");


        // jika mewakili provinsi
        if ($category->classification_country_id == 102) {
            $participant_member_team = $participant_member_team->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
        } else {
            $participant_member_team = $participant_member_team->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
        }


        // jika mewakili kota
        if ($category->classification_country_id == 102) {
            $participant_member_team = $participant_member_team->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
        } else {
            $participant_member_team = $participant_member_team->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
        }


        // jika berasal dari settingan admin
        $participant_member_team = $participant_member_team->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

        $participant_member_team = $participant_member_team->where('archery_event_participants.event_category_id', $category->id)
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
                    $pmt->parent_classification_type = $parent_classifification_id;
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

    public static function setMemberBudrest($category_id)
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

            $jadwal =  ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member->id)->first();
            if (!$jadwal) {
                $jadwal = new ArcheryEventQualificationScheduleFullDay();
            }

            $jadwal->qalification_time_id = $q_time->id;
            $jadwal->participant_member_id = $participant_member->id;
            $jadwal->save();
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

        $category_detail = ArcheryEventCategoryDetail::find($category_id);
        if (!$category_detail) {
            throw new BLoCException("category not found");
        }

        $event = ArcheryEvent::find($category_detail->event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        $parent_classifification_id = $event->parent_classification;

        if ($parent_classifification_id == 0) {
            throw new BLoCException("parent calassification_id invalid");
        }

        $tag = "club_id";
        $select_classification_query = "archery_event_participants.club_id";

        if ($parent_classifification_id == 2) { // jika mewakili negara
            $tag = "classification_country_id";
            $select_classification_query = "archery_event_participants.classification_country_id";
        }

        if ($parent_classifification_id == 3) { // jika mewakili provinsi
            $tag = "classification_province_id";
            $select_classification_query = "archery_event_participants.classification_province_id";
        }

        if ($parent_classifification_id == 4) { // jika mewakili kota
            $tag = "city_id";
            $select_classification_query = "archery_event_participants.city_id";
        }

        if ($parent_classifification_id > 5) { // jika berasal dari settingan admin
            $tag = "children_classification_id";
            $select_classification_query = "archery_event_participants.children_classification_id";
        }

        $data = ArcheryEventParticipant::select($select_classification_query);

        if ($parent_classifification_id == 1) { // jika mewakili club
            $data = $data->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
        }

        if ($parent_classifification_id == 2) { // jika mewakili negara
            $data = $data->join("countries", "countries.id", "=", "archery_event_participants.classification_country_id");
        }

        if ($parent_classifification_id == 3) { // jika mewakili provinsi
            if ($event->classification_country_id == 102) {
                $data = $data->join("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
            } else {
                $data = $data->join("states", "states.id", "=", "archery_event_participants.classification_province_id");
            }
        }

        if ($parent_classifification_id == 4) { // jika mewakili kota
            if ($event->classification_country_id == 102) {
                $data = $data->join("cities", "cities.id", "=", "archery_event_participants.city_id");
            } else {
                $data = $data->join("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
            }
        }

        if ($parent_classifification_id > 5) { // jika berasal dari settingan admin
            $data = $data->join("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");
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
        }

        $schedules = ArcheryEventQualificationScheduleFullDay::select(
            "archery_event_qualification_schedule_full_day.*",
            $select_classification_query
        )
            ->join("archery_event_participant_members", "archery_event_qualification_schedule_full_day.participant_member_id", "=", "archery_event_participant_members.id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("qalification_time_id", $qualification_time->id)
            ->get();



        foreach ($schedules as $key => $value) {
            if (isset($club_or_city_ids[$value[$tag]])) {
                $club_or_city_ids[$value[$tag]]["total"] += 1;
            }
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
        $category = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            "archery_events.is_private",
            "archery_events.parent_classification",
            "archery_events.classification_country_id"
        )
            ->join("archery_events", "archery_events.id", "=", "archery_event_category_details.event_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();

        $parent_classifification_id = $category->parent_classification;

        if ($parent_classifification_id == 0) {
            throw new BLoCException("parent calassification_id invalid");
        }

        $path = 'asset/score_sheet/' . $category->id . '/';
        if (!$update_file) {
            if (file_exists(public_path() . "/" . $path . "score_sheet_" . $category->id . ".pdf")) {
                return ["url" => $path . "score_sheet_qualification_selection" . $category->id . ".pdf#oldData", "member_not_have_budrest" => []];
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
            "archery_event_participants.club_id",
            "archery_clubs.name as club_name",
            "archery_event_participants.city_id",
            $category->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
            "archery_event_participants.classification_country_id",
            "countries.name as country_name",
            "archery_event_participants.classification_province_id",
            $category->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
            "archery_event_participants.children_classification_id",
            "children_classification_members.title as children_classification_members_name"
        )
            ->leftJoin('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'archery_event_participant_members.id')
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('users', 'users.id', '=', 'archery_event_participants.user_id');
        // jika mewakili club
        $participant_member_team = $participant_member_team->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");


        // jika mewakili negara
        $participant_member_team = $participant_member_team->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");


        // jika mewakili provinsi
        if ($category->classification_country_id == 102) {
            $participant_member_team = $participant_member_team->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
        } else {
            $participant_member_team = $participant_member_team->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
        }


        // jika mewakili kota
        if ($category->classification_country_id == 102) {
            $participant_member_team = $participant_member_team->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
        } else {
            $participant_member_team = $participant_member_team->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
        }


        // jika berasal dari settingan admin
        $participant_member_team = $participant_member_team->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

        $participant_member_team = $participant_member_team->where('archery_event_participants.event_category_id', $category->id)
            ->where('archery_event_participants.status', 1)
            ->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number", "ASC")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face", "ASC")
            ->get();

        $array_pesrta_baru = [];

        for ($i = 1; $i <= $category->session_in_qualification; $i++) {
            if ($i == $session) {
                foreach ($participant_member_team as $pmt) {
                    $pmt->parent_classification_type = $parent_classifification_id;
                    $code_sesi['detail_member'] = $pmt;
                    $code_sesi['sesi'] = $i;
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
        $category = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            "archery_events.is_private",
            "archery_events.parent_classification",
            "archery_events.classification_country_id"
        )
            ->join("archery_events", "archery_events.id", "=", "archery_event_category_details.event_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();

        $parent_classifification_id = $category->parent_classification;

        if ($parent_classifification_id == 0) {
            throw new BLoCException("parent calassification_id invalid");
        }

        $path = 'asset/score_sheet/' . $category->id . '/';
        if (!$update_file) {
            if (file_exists(public_path() . "/" . $path . "score_sheet_" . $category->id . ".pdf")) {
                return ["url" => $path . "score_sheet_elimination_selection" . $category->id . ".pdf#oldData", "member_not_have_budrest" => []];
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
            "archery_event_participants.club_id",
            "archery_clubs.name as club_name",
            "archery_event_participants.city_id",
            $category->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
            "archery_event_participants.classification_country_id",
            "countries.name as country_name",
            "archery_event_participants.classification_province_id",
            $category->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
            "archery_event_participants.children_classification_id",
            "children_classification_members.title as children_classification_members_name"
        )
            ->leftJoin('archery_event_qualification_schedule_full_day', 'archery_event_qualification_schedule_full_day.participant_member_id', '=', 'archery_event_participant_members.id')
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('users', 'users.id', '=', 'archery_event_participants.user_id');

        // jika mewakili club
        $participant_member_team = $participant_member_team->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");


        // jika mewakili negara
        $participant_member_team = $participant_member_team->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");


        // jika mewakili provinsi
        if ($category->classification_country_id == 102) {
            $participant_member_team = $participant_member_team->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
        } else {
            $participant_member_team = $participant_member_team->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
        }


        // jika mewakili kota
        if ($category->classification_country_id == 102) {
            $participant_member_team = $participant_member_team->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
        } else {
            $participant_member_team = $participant_member_team->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
        }


        // jika berasal dari settingan admin
        $participant_member_team = $participant_member_team->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

        $participant_member_team = $participant_member_team->where('archery_event_participants.event_category_id', $category->id)
            ->where('archery_event_participants.status', 1)
            ->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number", "ASC")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face", "ASC")
            ->get();

        $count_stage = $category->count_stage_elimination_selection;
        $array_pesrta_baru = [];

        for ($i = 1; $i <= $count_stage; $i++) {
            if ($i == $session) {
                foreach ($participant_member_team as $pmt) {
                    $pmt->parent_classification_type = $parent_classifification_id;
                    $code_sesi['detail_member'] = $pmt;
                    $code_sesi['sesi'] = $i;
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
        $i = 1;
        foreach ($output['data_member'] as $m) {
            if ($m["detail_member"]["bud_rest_number"] == 0) {
                throw new BLoCException("masih ada peserta yang belum memiliki bantalan");
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i][] = $m;
            if (count($member_in_budrest[$m["detail_member"]["bud_rest_number"]]["members"][$i]) >= 2) {
                $i++;
            }
            $member_in_budrest[$m["detail_member"]["bud_rest_number"]]['code'] = "4-" . $category->id . "-" . $session . "-" . $m["detail_member"]["bud_rest_number"];
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
                        $html = \view('template.event_selection.score_sheet_elimination', [
                            "data" => $dm[0],
                            "category" => $output['category'],
                            "category_label" => $output['category_label'],
                            "qr" => $base64,
                            "total_shot_per_stage" => $category->count_shot_in_stage,
                            "total_stage" => $count_stage,
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
                        $html = \view('template.event_selection.score_sheet_elimination_group_by_budrest', [
                            "data" => $data["members"],
                            "category" => $output['category'],
                            "category_label" => $output['category_label'],
                            "total_shot_per_stage" => $category->count_shot_in_stage,
                            "total_stage" => $count_stage,
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
                $html = \view('template.event_selection.score_sheet_elimination_group_by_budrest', [
                    "data" => $data["members"],
                    "category" => $output['category'],
                    "category_label" => $output['category_label'],
                    "total_shot_per_stage" => $category->count_shot_in_stage,
                    "total_stage" => $count_stage,
                    "qr" => $base64,
                    "event" => $output['event'],
                    "row_height" => "40px",
                ]);
                $mpdf->AddPage("P");
                $mpdf->WriteHTML($html);
            }
        }

        $full_path = $path . "score_sheet_elimination_selection" . $category->id . ".pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return ["url" => $full_path, "member_not_have_budrest" => $member_not_have_budrest];
    }
}
