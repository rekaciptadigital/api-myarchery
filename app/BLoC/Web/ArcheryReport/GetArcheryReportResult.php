<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryClub;
use Mpdf\Mpdf;

use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryEventEliminationMatch;
use DAI\Utils\Helpers\BLoC;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Http\Services\PDFService;
use Illuminate\Support\Facades\Storage;
use PDFv2;
use Knp\Snappy\Pdf;



class GetArcheryReportResult extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $data_report=[];
        $event_id=$parameters->get('event_id');
        $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))->where("event_id",$event_id)
        ->orderBy('competition_category_id', 'DESC')->get();
        $file_name="sdasd.pdf";
        $html='csdfas';
        $pdf = PDFv2::loadView('report_result', [
            'competition_category' => $competition_category            
        ]); 

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
        // return $pdf->download();

        $path       = 'download';          
        if (!file_exists(public_path()."/".$path)) {
            mkdir(public_path()."/".$path, 0775);
        }
        // $filePath   = Storage::path($path);
        $filePath   = Storage::disk('public')->path($path);
        $fileName   = 'grafik_data_dampak_.pdf';
        $generate   = $pdf->save(''.$filePath.'/'.$fileName.'');
        
        $response = [
            'file_path' => $disk->url($path.$fileName)
        ];
        
        return Response::api("Data berhasil diunduh", $response);
       
       
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => 'required|integer'
        ];
    }
}
