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
        $path = 'asset/score_sheet/sfasfa.pdf';
        if (!file_exists(public_path()."/".$path)) {
            mkdir(public_path()."/".$path, 0777);
        }
        $full_path = $path ;
        $filePath= env('APP_HOSTNAME') . $full_path;
        
         
        $generate   = (new PDFService())->generate($html, $filePath, $file_name);
     
        $response = [
            'file_path' => $filePath
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
