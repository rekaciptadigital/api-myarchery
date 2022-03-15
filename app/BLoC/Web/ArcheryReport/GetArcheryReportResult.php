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

        if(!$competition_category) throw new BLoCException("tidak ada data kategori terdaftar untuk event tersebut");    
        

        foreach ($competition_category as $competition) {
            $age_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct age_category_id as age_category'))->where("event_id",$event_id)
            ->where("competition_category_id",$competition->competition_category)
            ->orderBy('competition_category_id', 'DESC')->get();

            if(!$age_category) throw new BLoCException("tidak ada data age category terdaftar untuk event tersebut");    
        
            foreach ($age_category as $age) {
                $distance_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct distance_id as distance_category'))->where("event_id",$event_id)
                ->where("competition_category_id",$competition->competition_category)
                ->where("age_category_id",$age->age_category)
                ->orderBy('competition_category_id', 'DESC')->get();

                if(!$distance_category) throw new BLoCException("tidak ada data distance category terdaftar untuk event tersebut");    
        

                foreach ($distance_category as $distance) {
                    $team_category = ArcheryEventCategoryDetail::select(DB::RAW('team_category_id as team_category'))->where("event_id",$event_id)
                    ->where("competition_category_id",$competition->competition_category)
                    ->where("age_category_id",$age->age_category)
                    ->where("distance_id",$distance->distance_category)
                    ->leftJoin("archery_master_team_categories",'archery_master_team_categories.id','archery_event_category_details.team_category_id')
                    ->orderBy("archery_master_team_categories.short", "ASC")->get();

                    if(!$team_category) throw new BLoCException("tidak ada data team category terdaftar untuk event tersebut");    
        
                    foreach ($team_category as $team) {
                        $members_elimination = ArcheryEventEliminationMember::select("*","archery_event_category_details.id as category_details_id",DB::RAW('date(archery_event_elimination_members.created_at) as date'))
                        ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'archery_event_elimination_members.member_id')
                        ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                        ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'archery_event_participants.event_category_id')
                        //->where("is_series",1)
                        ->where("archery_event_category_details.competition_category_id",$competition->competition_category)
                        ->where("archery_event_category_details.age_category_id",$age->age_category)
                        ->where("archery_event_category_details.distance_id",$distance->distance_category)
                        ->where("archery_event_category_details.team_category_id",$team->team_category)
                        ->where("archery_event_participants.event_id",$event_id)
                        ->where("archery_event_elimination_members.elimination_ranked",'>',0)
                        ->where("archery_event_elimination_members.elimination_ranked",'<=',3)
                        ->orderBy('archery_event_participants.event_category_id', 'ASC')
                        ->orderBy('archery_event_category_details.team_category_id', 'DESC')
                        ->orderBy('archery_event_elimination_members.elimination_ranked', 'ASC')
                        ->get();

                        if ($members_elimination) { 
                            foreach ($members_elimination as $member) {
                                $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($member->category_details_id);
                                
                                if($member->elimination_ranked==1){
                                    $medal='Gold';
                                }else if ($member->elimination_ranked==2){
                                    $medal='Silver';
                                }else {
                                    $medal='Bronze';
                                }
                                
                                $athlete=$member->name;
                                $date=$member->date;
                                            
                                $club = ArcheryClub::find($member->club_id);
                                if(!$club){
                                    $club='';
                                }else{
                                    $club=$club->name;
                                }
                                if ($competition->competition_category){
                                    $report=$competition->competition_category. ' - Elimination';
                                }
                                $data_report[]=array("athlete" => $athlete, "club" => $club,"category" => $categoryLabel,"medal" => $medal, "date" =>$date );
                            }
                        } 

                        

                    }
                    if($data_report){

                       

                        $html = view('report_result',[
                        'data_report' => $data_report,  
                        'competition' => $competition->competition_category,
                        'report' => $report,
                        'category' =>' '
                        ]);
                        $mpdf->WriteHTML($html);
                        $mpdf->AddPage();
                    }
                    $data_report = array();

                   
                    foreach ($team_category as $team) {
                        $members_qualificaiton = ArcheryEventEliminationMember::select("*","archery_event_category_details.id as category_details_id",DB::RAW('date(archery_event_elimination_members.created_at) as date'))
                        ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'archery_event_elimination_members.member_id')
                        ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                        ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'archery_event_participants.event_category_id')
                        //->where("is_series",1)
                        ->where("archery_event_category_details.competition_category_id",$competition->competition_category)
                        ->where("archery_event_category_details.age_category_id",$age->age_category)
                        ->where("archery_event_category_details.distance_id",$distance->distance_category)
                        ->where("archery_event_category_details.team_category_id",$team->team_category)
                        ->where("archery_event_participants.event_id",$event_id)
                        ->where("archery_event_elimination_members.position_qualification",'>',0)
                        ->where("archery_event_elimination_members.position_qualification",'<=',3)
                        ->orderBy('archery_event_participants.event_category_id', 'ASC')
                        ->orderBy('archery_event_category_details.team_category_id', 'DESC')
                        ->orderBy('archery_event_elimination_members.position_qualification', 'ASC')
                        ->get();

                        if ($members_qualificaiton) { 
                            
                            foreach ($members_qualificaiton as $member) {
                                $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($member->category_details_id);
                                
                                if($member->position_qualification==1){
                                    $medal='Gold';
                                }else if ($member->position_qualification==2){
                                    $medal='Silver';
                                }else {
                                    $medal='Bronze';
                                }
                                
                                $athlete=$member->name;
                                $date=$member->date;
                                            
                                $club = ArcheryClub::find($member->club_id);
                                if(!$club){
                                    $club='';
                                }else{
                                    $club=$club->name;
                                }
                                if ($competition->competition_category){
                                    $report=$competition->competition_category. ' - Qualification';
                                }
                                
                                $data_report[]=array("athlete" => $athlete, "club" => $club,"category" => $categoryLabel,"medal" => $medal, "date" =>$date );
                                
                                
                            }
                        } 
                        
                        if($data_report){
                            $html = view('report_result',[
                            'data_report' => $data_report,  
                            'competition' => $competition->competition_category,
                            'report' => $report,
                            'category' => $categoryLabel
                            ]);
                            
                            $mpdf->WriteHTML($html);
                            
                        }
                        if($data_report){
                            $mpdf->AddPage();
                        }

                    }
                    //buat pengecekan kalo sebelumnya ada data winner baru print seluruh pesertanya
                    if($data_report){
                        $data_report = array();
                        foreach ($team_category as $team) {
                            $members_qualificaiton = ArcheryEventEliminationMember::select("*","archery_event_category_details.id as category_details_id",DB::RAW('date(archery_event_elimination_members.created_at) as date'))
                            ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'archery_event_elimination_members.member_id')
                            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                            ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'archery_event_participants.event_category_id')
                            //->where("is_series",1)
                            ->where("archery_event_category_details.competition_category_id",$competition->competition_category)
                            ->where("archery_event_category_details.age_category_id",$age->age_category)
                            ->where("archery_event_category_details.distance_id",$distance->distance_category)
                            ->where("archery_event_category_details.team_category_id",$team->team_category)
                            ->where("archery_event_participants.event_id",$event_id)
                            ->orderBy('archery_event_participants.event_category_id', 'ASC')
                            ->orderBy('archery_event_category_details.team_category_id', 'DESC')
                            ->orderBy('archery_event_elimination_members.position_qualification', 'ASC')
                            ->get();

                            if ($members_qualificaiton) { 
                                
                                foreach ($members_qualificaiton as $member) {
                                    $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($member->category_details_id);
                                    
                                    $athlete=$member->name;
                                    $date=$member->date;
                                                
                                    $club = ArcheryClub::find($member->club_id);
                                    if(!$club){
                                        $club='';
                                    }else{
                                        $club=$club->name;
                                    }
                                    $report='Result';
                                    
                                    $data_report[]=array("athlete" => $athlete, "club" => $club,"category" => $categoryLabel);
                                    
                                }
                            } 
                            
                            if($data_report){
                                $html = view('report_result_all',[
                                'data_report' => $data_report,  
                                'competition' => $competition->competition_category,
                                'report' => $report,
                                'category' => $categoryLabel
                                ]);
                                
                                $mpdf->WriteHTML($html);
                                
                            }
                            if($data_report){
                                $data_report = array();
                                $mpdf->AddPage();
                            }

                        }
                    }
                    
                    
                    
                }  
                
                

            }
            
        }

        
        
        $path = 'asset/report/';
        $full_path = $path . "report_result.pdf";
        $mpdf->Output(public_path() . "/" . $full_path, "F");
        return env('APP_HOSTNAME') . $full_path;
        
       
       
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => 'required|integer'
        ];
    }
}
