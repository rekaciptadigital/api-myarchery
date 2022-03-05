<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventSerie;
use App\Models\ArcherySeriesUserPoint;
use App\Models\ArcheryEventElimination;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventParticipantNumber;
use App\Libraries\EliminationFormat;
use App\Libraries\ClubRanked;
use App\Models\User;
class GenerateTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:task {param}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'untuk runing query generate dat bnyak';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cat_id = $this->argument('param');
        // ClubRanked::getEventRanked(21);
        // ArcherySeriesUserPoint::setAutoUserMemberCategory(21);
        // ArcherySeriesUserPoint::setMemberQualificationPoint($cat_id);
        
        
        
        // $cat_id = $this->argument('category');
        // $elimination = ArcheryEventElimination::where("gender","none")->get();
        // foreach ($elimination as $key => $value) {
        //     $match = ArcheryEventEliminationMatch::where("event_elimination_id",$value->id)->where("elimination_member_id","!=",0)->get();
        //     foreach ($match as $m => $mat) {
        //         $member = ArcheryEventEliminationMember::find($mat->elimination_member_id);
        //         $champion = EliminationFormat::EliminationChampion($value->count_participant,$mat->round,$mat->match,$mat->win);
        //         $check = ArcherySeriesUserPoint::where("member_id",$member->member_id)->where("type","elimination")->count();
        //         if($champion != 0 && $check < 1)
        //             ArcherySeriesUserPoint::setPoint($member->member_id,"elimination",$champion);
        //     }
        // }


        // $category = ArcheryEventCategoryDetail::find($cat_id);
        // if(!$category) return false;

        // $event_serie = ArcheryEventSerie::where("event_id",$category->event_id)->first();
        // if(!$event_serie) return false;
        
        // $archerySeriesCategory = ArcherySeriesCategory::where("age_category_id", $category->age_category_id)
        // ->where("competition_category_id", $category->competition_category_id)
        // ->where("distance_id", $category->distance_id)
        // ->where("team_category_id", $category->team_category_id)
        // ->where("serie_id", $event_serie->serie_id)
        // ->first();
        
        // if(!$archerySeriesCategory) return false;

        // ArcherySeriesUserPoint::where("event_category_id", $category->id)->update([
        //     "event_category_id" => $archerySeriesCategory->id
        // ]);
        
        // $p = ArcheryEventParticipant::where("event_id",21)->where("status",1)->get();
        // foreach ($p as $key => $value) {
        //     $u = User::find($value->user_id);
        //     ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($value->event_category_id, $u->gender), $value->id);
        // }
    }
}
