<?php

namespace App\BLoC\Web\EliminationScoreSheet;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ParticipantMemberTeam;
use App\Models\BudRest;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Mpdf\Mpdf;

class DownloadEliminationScoreSheet extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $mpdf = new Mpdf([
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'mode' => 'utf-8',
            'format' => 'A4-P',
            'orientation' => 'P',
            'bleedMargin' => 0,
            'dpi'        => 110,
            'default_font_size' => 9,
            'shrink_tables_to_fit' => 1.4,
            'tempDir' => public_path() . '/tmp/pdf'
        ]);

        $html = view('template.score_sheet_elimination');
        $mpdf->WriteHTML($html);
        $path = 'asset/score_sheet/';
        $full_path = $path . "score_sheet_elimination.pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return env('APP_HOSTNAME') . $full_path;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
