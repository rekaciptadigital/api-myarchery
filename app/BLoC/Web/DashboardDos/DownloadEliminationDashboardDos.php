<?php

namespace App\BLoC\Web\DashboardDos;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationSchedule;

use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Storage;
use PDFv2;
use App\Libraries\EliminationFormatPDF;

class DownloadEliminationDashboardDos extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $score_type = 1;
        $event_category_id = $parameters->get('event_category_id');
        $category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        $event = ArcheryEvent::find($category_detail->event_id);
        $elimination = ArcheryEventElimination::where("event_category_id", $event_category_id)->first();

        if (!$elimination) {
            throw new BLoCException("event elimination not found");
        }

        $data_graph = EliminationFormatPDF::getDataGraph($event_category_id);
        if ($data_graph) {
            $title = $event->event_name .' ('. $category_detail->label_category . ')';
            if ($elimination->count_participant == 16) {
                $data = EliminationFormatPDF::getViewDataGraph16($data_graph);
                $view_path = 'reports/dashboard_dos/elimination/graph_sixteen';
                $pages[] = EliminationFormatPDF::renderPageGraph16($view_path, $data, $title);
            } else if ($elimination->count_participant == 8) {
                $data = EliminationFormatPDF::getViewDataGraph8_reportDos($data_graph);
                $view_path = 'reports/dashboard_dos/elimination/graph_eight';
                $pages[] = EliminationFormatPDF::renderPageGraph8($view_path, $data, $title);
            } else {
                throw new BLoCException("sorry, elimination template not found");
            }
            
        }

        $pdf = PDFv2::loadView('report_result/all', ['pages' => $pages]);
        $pdf->setOptions([
            'margin-top'    => 10,
            'margin-bottom' => 15,
            'page-size'     => 'a4',
            'orientation'   => 'portrait',
            'enable-javascript' => true,
            'javascript-delay' => 9000,
            'no-stop-slow-scripts' => true,
            'enable-smart-shrinking' => true
        ]);

        $digits = 3;
        $fileName   = $event->event_name . ' - Elimination - ' . $category_detail->label_category. ' - '. date("YmdHis") . '.pdf';
        $path = 'asset/dashboard_dos';
        $generate   = $pdf->save('' . $path . '/' . $fileName . '');
        $response = url(env('APP_HOSTNAME') . $path . '/' . $fileName . '');

        return $response;
    }

    protected function validation($parameters)
    {
        return [
            'event_category_id' => 'required',
        ];
    }
}