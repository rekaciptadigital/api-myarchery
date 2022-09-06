<?php

namespace App\BLoC\Web\EliminationScoreSheet;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Mpdf\Mpdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class DownloadEmptyScoreSheetElimination extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_id = $parameters->get("category_id");

        $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();

        $archery_event = ArcheryEvent::find($category->event_id);
        if (!$archery_event) {
            throw new BLoCException("event not found");
        }

        $label = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);

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

        $string_code = "https://myarchery.id";
        $qrCode = new QrCode($string_code);
        $output_qrcode = new Output\Png();
        $qrCode_name_file = "qr_code_empty.png";
        $full_path = $path . $qrCode_name_file;
        $data_qr_code =  $output_qrcode->output($qrCode,  100, [255, 255, 255], [0, 0, 0]);
        file_put_contents(public_path() . '/' . $full_path, $data_qr_code);
        $data_get_qr_code = file_get_contents(public_path() . "/" . $full_path);
        $base64 = 'data:image/png;base64,' . base64_encode($data_get_qr_code);

        if (strtolower($category->type) == "team") {
            $html = view('template.score_sheet_elimination_team', [
                'tim_1_name' => "",
                'tim_2_name' => "",
                'club_1' => "",
                'club_2' => "",
                'tim_1_rank' => "",
                'tim_2_rank' => "",
                "athlete_1" => "",
                "athlete_2" => "",
                "budrest_1" => "",
                "budrest_2" => "",
                'tim1_category' => $label,
                'tim2_category' => $label,
                "qr" => "",
                "event_name" => $event_name,
                "location" => $location_event
            ]);
        } else {
            $html = view('template.score_sheet_elimination', [
                'peserta1_name' => "",
                'peserta2_name' => "",
                'peserta1_club' => "",
                'peserta2_club' => "",
                'peserta1_rank' => "",
                'peserta2_rank' => "",
                'peserta1_category' => $label,
                'peserta2_category' => $label,
                "qr" => "",
                "event_name" => $event_name,
                "location" => $location_event
            ]);
        }

        $mpdf->WriteHTML($html);
        $full_path = $path . "empty_score_sheet_elimination.pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return env('APP_HOSTNAME') . $full_path;
    }

    protected function validation($parameters)
    {
        return [
            'category_id' => 'required|exists:archery_event_category_details,id'
        ];
    }
}
