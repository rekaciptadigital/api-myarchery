<?php

namespace App\Exports\Sheets;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventIdcardTemplate;
use App\Models\User;
use App\Models\ArcheryMasterTeamCategory;

use Maatwebsite\Excel\Concerns\FromCollection;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryUserAthleteCode;

class SummaryParticipantSheet implements FromView, WithColumnWidths, WithHeadings
{
    protected $event_id;

    function __construct($event_id) {
            $this->event_id = $event_id;
    }

    public function view(): View
    {
        $event_id=$this->event_id ;
        $admin = Auth::user();
        $event_detail = ArcheryEvent::find($event_id);
        $team_category = DB::select('SELECT archery_event_category_details.team_category_id, sum(archery_event_category_details.quota) as total_quota 
                                FROM archery_event_category_details 
                                WHERE archery_event_category_details.event_id = ? 
                                GROUP BY(archery_event_category_details.team_category_id)',[$event_id]);
        
        $competition_category = DB::select('SELECT archery_event_category_details.competition_category_id, sum(archery_event_category_details.quota) as total_quota 
                                FROM archery_event_category_details 
                                WHERE archery_event_category_details.event_id = ? 
                                GROUP BY(archery_event_category_details.competition_category_id)',[$event_id]);
        
        if(empty($team_category)){
            throw new BLoCException("data tidak ditemukan");
        }
        
        $team_category_obj = [];
        foreach ($team_category as $key => $value) {
            $team_category_id = $value->team_category_id;
            $team_category_detail = ArcheryMasterTeamCategory::where("id",$team_category_id)->first();
            $category = ArcheryEventCategoryDetail::where("archery_event_category_details.event_id",$event_id)->where("archery_event_category_details.team_category_id",$team_category_id)->first();
            $check_participant = DB::select('SELECT count(archery_event_participants.id) as total_register
            FROM archery_event_participants
            JOIN archery_event_category_details ON archery_event_participants.event_category_id = archery_event_category_details.id
            WHERE archery_event_category_details.event_id = ? 
            AND archery_event_category_details.team_category_id = ? 
            AND archery_event_participants.status = 1 
            GROUP BY(archery_event_category_details.team_category_id)',[$event_id,$team_category_id]);
            $total_sell = $check_participant ? $check_participant[0]->total_register : 0;
            $fee = $category ? $category->fee : 0;
            $team_category_obj[$team_category_id] = [
                "quota" => $value->total_quota,
                "fee" => $fee,
                "label" => $team_category_detail->label,
                "total_sell" => $total_sell,
                "total_amount" => $fee * $total_sell,
                "left_quota" => $value->total_quota - $total_sell,
            ];
        }
        
        $competition_category_obj = [];
        foreach($competition_category as $key => $value){
            $competition_category_id = $value->competition_category_id;
            $individual = DB::select('SELECT sum(archery_event_category_details.quota) as total_quota 
                                FROM archery_event_category_details 
                                WHERE archery_event_category_details.event_id = ? 
                                AND archery_event_category_details.team_category_id IN (?,?) 
                                GROUP BY(archery_event_category_details.competition_category_id)',[$event_id,"individu male","individu female"]);
            $team = DB::select('SELECT sum(archery_event_category_details.quota) as total_quota 
                                FROM archery_event_category_details 
                                WHERE archery_event_category_details.event_id = ? 
                                AND archery_event_category_details.team_category_id IN (?,?) 
                                GROUP BY(archery_event_category_details.competition_category_id)',[$event_id,"male_team","female_team"]);
            $mix_team = DB::select('SELECT sum(archery_event_category_details.quota) as total_quota 
                                FROM archery_event_category_details 
                                WHERE archery_event_category_details.event_id = ? 
                                AND archery_event_category_details.team_category_id IN (?) 
                                GROUP BY(archery_event_category_details.competition_category_id)',[$event_id,"mix_team"]);
            $check_participant_mix = ArcheryEventParticipant::join("archery_event_category_details","archery_event_participants.event_category_id","archery_event_category_details.id")
                                     ->where("archery_event_category_details.event_id", $event_id)
                                     ->where("archery_event_category_details.competition_category_id", $competition_category_id)
                                     ->where("archery_event_participants.status", 1)
                                     ->whereIn("archery_event_category_details.team_category_id", ["mix_team"])
                                     ->groupBy("archery_event_category_details.competition_category_id")->count();
            $check_participant_individu = ArcheryEventParticipant::join("archery_event_category_details","archery_event_participants.event_category_id","archery_event_category_details.id")
                                     ->where("archery_event_category_details.event_id", $event_id)
                                     ->where("archery_event_category_details.competition_category_id", $competition_category_id)
                                     ->where("archery_event_participants.status", 1)
                                     ->whereIn("archery_event_category_details.team_category_id", ["individu male","individu female"])
                                     ->groupBy("archery_event_category_details.competition_category_id")->count();
            $check_participant_team = ArcheryEventParticipant::join("archery_event_category_details","archery_event_participants.event_category_id","archery_event_category_details.id")
                                     ->where("archery_event_category_details.event_id", $event_id)
                                     ->where("archery_event_category_details.competition_category_id", $competition_category_id)
                                     ->where("archery_event_participants.status", 1)
                                     ->whereIn("archery_event_category_details.team_category_id", ["male_team","female_team"])
                                     ->groupBy("archery_event_category_details.competition_category_id")->count();
            $fee_individu = $team_category_obj["individu male"]["fee"];
            $fee_team = $team_category_obj["male_team"]["fee"];
            $fee_mix_team = $team_category_obj["mix_team"]["fee"];
            $competition_category_obj[$competition_category_id]=[
                'label' => $value->competition_category_id,
                'fee' => [
                    "individu" => $fee_individu,
                    "team" => $fee_team,
                    "mix_team" => $fee_mix_team,
                ],
                'quota' => [
                    "individu" => $individual[0]->total_quota,
                    "team" => $team[0]->total_quota,
                    "mix_team" => $mix_team[0]->total_quota
                ],
                'total_sell' => [
                    "individu" => $check_participant_individu,
                    "team" => $check_participant_team,
                    "mix_team" => $check_participant_mix,
                ],
                'remaining_quota' => [
                    "individu" => $individual[0]->total_quota - $check_participant_individu,
                    "team" => $team[0]->total_quota - $check_participant_team,
                    "mix_team" => $mix_team[0]->total_quota - $check_participant_mix
                ],
                'total_amount' => ($check_participant_individu * $fee_individu) + ($check_participant_team * $fee_team) + ($check_participant_mix * $fee_mix_team),
            ];
        }


        $team_obj = [
            [
                "Individual Putra & putri" =>[
                    "amount" => $team_category_obj["individu male"]["fee"],
                    "quota" => $team_category_obj["individu male"]["quota"] + $team_category_obj["individu female"]["quota"],
                    "quota_sell" => $team_category_obj["individu male"]["total_sell"] + $team_category_obj["individu female"]["total_sell"],
                    "left_quota" => $team_category_obj["individu male"]["left_quota"] + $team_category_obj["individu female"]["left_quota"],
                    "total_amount" => $team_category_obj["individu male"]["total_amount"] + $team_category_obj["individu female"]["total_amount"],
                ],
                "Beregu Putra & putri" =>[
                    "amount" => $team_category_obj["male_team"]["fee"],
                    "quota" => $team_category_obj["male_team"]["quota"] + $team_category_obj["female_team"]["quota"],
                    "quota_sell" => $team_category_obj["male_team"]["total_sell"] + $team_category_obj["female_team"]["total_sell"],
                    "left_quota" => $team_category_obj["male_team"]["left_quota"] + $team_category_obj["female_team"]["left_quota"],
                    "total_amount" => $team_category_obj["male_team"]["total_amount"] + $team_category_obj["female_team"]["total_amount"],
                ],
                "Beregu Campuran" =>[
                    "amount" => $team_category_obj["mix_team"]["fee"],
                    "quota" => $team_category_obj["mix_team"]["quota"],
                    "quota_sell" => $team_category_obj["mix_team"]["total_sell"],
                    "left_quota" => $team_category_obj["mix_team"]["left_quota"],
                    "total_amount" => $team_category_obj["mix_team"]["total_amount"],
                ]
            ]
        ];

        $gender_obj = [
            "Putra" =>[
                "total_participant" => $team_category_obj["individu male"]["total_sell"],
            ],
            "Putri" => [
                "total_participant" => $team_category_obj["individu female"]["total_sell"],
            ]
        ];
        
        $public_summary = DB::select('SELECT archery_master_age_categories.label as age_category_label, archery_master_age_categories.id as age_category_id
                                            ,archery_master_competition_categories.label as competition_category_label, archery_master_competition_categories.id as competition_category_id
                                            ,archery_master_distances.label as distance_label, archery_master_distances.id as distance_id
                                            , sum(quota) as total_quota 
                                FROM archery_event_category_details 
                                JOIN archery_master_age_categories ON archery_event_category_details.age_category_id = archery_master_age_categories.id  
                                JOIN archery_master_competition_categories ON archery_event_category_details.competition_category_id = archery_master_competition_categories.id  
                                JOIN archery_master_distances ON archery_event_category_details.distance_id = archery_master_distances.id  
                                WHERE event_id = ? 
                                GROUP BY age_category_id,competition_category_id,distance_id',[$event_id]);

        $public_summary_obj = [];
        foreach ($public_summary as $key => $value) {
            $individu_male_quota = ArcheryEventCategoryDetail::where("event_id",$event_id)
            ->where("age_category_id",$value->age_category_id)
            ->where("competition_category_id",$value->competition_category_id)
            ->where("distance_id",$value->distance_id)
            ->where("team_category_id","individu male")
            ->groupBy(["age_category_id","competition_category_id","distance_id"])->sum("archery_event_category_details.quota");
            $individu_female_quota = ArcheryEventCategoryDetail::where("event_id",$event_id)
            ->where("age_category_id",$value->age_category_id)
            ->where("competition_category_id",$value->competition_category_id)
            ->where("distance_id",$value->distance_id)
            ->where("team_category_id","individu female")
            ->groupBy(["age_category_id","competition_category_id","distance_id"])->sum("archery_event_category_details.quota");
            $team_male_quota = ArcheryEventCategoryDetail::where("event_id",$event_id)
            ->where("age_category_id",$value->age_category_id)
            ->where("competition_category_id",$value->competition_category_id)
            ->where("distance_id",$value->distance_id)
            ->where("team_category_id","male_team")
            ->groupBy(["age_category_id","competition_category_id","distance_id"])->sum("archery_event_category_details.quota");
            $team_female_quota = ArcheryEventCategoryDetail::where("event_id",$event_id)
            ->where("age_category_id",$value->age_category_id)
            ->where("competition_category_id",$value->competition_category_id)
            ->where("distance_id",$value->distance_id)
            ->where("team_category_id","female_team")
            ->groupBy(["age_category_id","competition_category_id","distance_id"])->sum("archery_event_category_details.quota");
            $team_mix_quota = ArcheryEventCategoryDetail::where("event_id",$event_id)
            ->where("age_category_id",$value->age_category_id)
            ->where("competition_category_id",$value->competition_category_id)
            ->where("distance_id",$value->distance_id)
            ->where("team_category_id","mix_team")
            ->groupBy(["age_category_id","competition_category_id","distance_id"])->sum("archery_event_category_details.quota");
            
            $check_participant_male = ArcheryEventParticipant::join("archery_event_category_details","archery_event_participants.event_category_id","archery_event_category_details.id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->where("archery_event_category_details.age_category_id",$value->age_category_id)
            ->where("archery_event_category_details.competition_category_id",$value->competition_category_id)
            ->where("archery_event_category_details.distance_id",$value->distance_id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_category_details.team_category_id","individu male")
            ->groupBy(["archery_event_category_details.age_category_id","archery_event_category_details.competition_category_id","archery_event_category_details.distance_id"])->count();
            $check_participant_female = ArcheryEventParticipant::join("archery_event_category_details","archery_event_participants.event_category_id","archery_event_category_details.id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->where("archery_event_category_details.age_category_id",$value->age_category_id)
            ->where("archery_event_category_details.competition_category_id",$value->competition_category_id)
            ->where("archery_event_category_details.distance_id",$value->distance_id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_category_details.team_category_id","individu female")
            ->groupBy(["archery_event_category_details.age_category_id","archery_event_category_details.competition_category_id","archery_event_category_details.distance_id"])->count();
            $check_participant_male_team = ArcheryEventParticipant::join("archery_event_category_details","archery_event_participants.event_category_id","archery_event_category_details.id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->where("archery_event_category_details.age_category_id",$value->age_category_id)
            ->where("archery_event_category_details.competition_category_id",$value->competition_category_id)
            ->where("archery_event_category_details.distance_id",$value->distance_id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_category_details.team_category_id","male_team")
            ->groupBy(["archery_event_category_details.age_category_id","archery_event_category_details.competition_category_id","archery_event_category_details.distance_id"])->count();
            $check_participant_female_team = ArcheryEventParticipant::join("archery_event_category_details","archery_event_participants.event_category_id","archery_event_category_details.id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->where("archery_event_category_details.age_category_id",$value->age_category_id)
            ->where("archery_event_category_details.competition_category_id",$value->competition_category_id)
            ->where("archery_event_category_details.distance_id",$value->distance_id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_category_details.team_category_id","female_team")
            ->groupBy(["archery_event_category_details.age_category_id","archery_event_category_details.competition_category_id","archery_event_category_details.distance_id"])->count();
            $check_participant_mix = ArcheryEventParticipant::join("archery_event_category_details","archery_event_participants.event_category_id","archery_event_category_details.id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->where("archery_event_category_details.age_category_id",$value->age_category_id)
            ->where("archery_event_category_details.competition_category_id",$value->competition_category_id)
            ->where("archery_event_category_details.distance_id",$value->distance_id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_category_details.team_category_id","mix_team")
            ->groupBy(["archery_event_category_details.age_category_id","archery_event_category_details.competition_category_id","archery_event_category_details.distance_id"])->count();
            
            
            $public_summary_obj[] = [
                "label" => $value->age_category_label." - ".$value->competition_category_label." - ".$value->distance_label,
                "individu_male" => [
                    "quota" => $individu_male_quota,
                    "sell" => $check_participant_male,
                    "left" => $individu_male_quota - $check_participant_male,
                ],
                "individu_female" => [
                    "quota" => $individu_female_quota,
                    "sell" => $check_participant_female,
                    "left" => $individu_female_quota - $check_participant_female,
                ],
                "male_team" => [
                    "quota" => $team_male_quota,
                    "sell" => $check_participant_male_team,
                    "left" => $team_male_quota - $check_participant_male_team,
                ],
                "female_team" => [
                    "quota" => $team_female_quota,
                    "sell" => $check_participant_female_team,
                    "left" => $team_female_quota - $check_participant_female_team,
                ],
                "mix_team" => [
                    "quota" => $team_mix_quota,
                    "sell" => $check_participant_mix,
                    "left" => $team_mix_quota - $check_participant_mix,
                ]
            ];
        }
     
        return view('reports.summary_participant', [
            'team_category' => $team_category_obj,
            'team' => $team_obj,
            'gender' => $gender_obj,
            'event' => $event_detail,
            'competition_category' => $competition_category_obj,
            'public_summary' => $public_summary_obj,
        ]);
    }

    public function headings(): array
    {
        return [
            'A' =>200,
            'B' => 200, 
            'C' => 200          
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,            
            'C' => 20,   
            'D' => 30,
            'E' => 30,
            'F' => 20,
            'G' => 30,
            'H' => 30,
            'I' => 25,
            'J' => 20,
            'K' => 30,
            'L' => 30,
            'M' => 25,
            'N' => 30,
            'O' => 30,
            'P' => 20,
            'Q' => 30,
        ];
    }
    
}


