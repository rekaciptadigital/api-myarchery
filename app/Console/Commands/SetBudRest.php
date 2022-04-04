<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\ArcheryEventQualificationTime;
use App\Models\BudRest;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;

class SetBudRest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:BudRest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set budrest';

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
        $tp = ["A", "B", "C", "D", "E", "F"];
        $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
        $event_categories = Redis::hgetall($key);
        foreach ($event_categories as $event_category_detail) {
            Redis::hdel($key, $event_category_detail);
            return;
            echo "----------------- Start Set Budrest[" . $event_category_detail . "] " . date("Y-m-d H:i:s") . "-----------------\n\n";
            // ArcheryEventQualificationScheduleFullDay
            $bud_rest = BudRest::where("archery_event_category_id", $event_category_detail)->first();
            if (!$bud_rest) {
                echo "(!) Budrest belum di set\n\n";
                echo "----------------- FINISH " . $event_category_detail . " -----------------\n\n";
                continue;
            }

            $qualification_time = ArcheryEventQualificationTime::where("category_detail_id", $event_category_detail)->get();
            $bud_rest_start = $bud_rest->bud_rest_start;
            $bud_rest_end = $bud_rest->bud_rest_end;

            $target_face = 1;
            $count = 0;
            foreach ($qualification_time as $time) {
                $schedules = ArcheryEventQualificationScheduleFullDay::select("archery_event_qualification_schedule_full_day.*", "archery_event_participants.club_id")
                    ->join("archery_event_participant_members", "archery_event_qualification_schedule_full_day.participant_member_id", "=", "archery_event_participant_members.id")
                    ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                    ->where("qalification_time_id", $time->id)->orderBy("archery_event_participants.club_id", "DESC")->get();

                $data_count = count($schedules);
                $check_budrest = ceil($data_count / $bud_rest->target_face);
                $data_budrest = [];
                $m_target_face = array_slice($tp, 0, $bud_rest->target_face);
                for ($i = 0; $i < $check_budrest; $i++) {
                    $tf = [];
                    $tmp_tp = $m_target_face;
                    for ($x = 0; $x < $bud_rest->target_face; $x++) {
                        // $tmp_i = rand(0,count($tmp_tp)-1);
                        $tf[] = $tmp_tp[$x];
                        // unset($tmp_tp[$tmp_i]); 
                        // $tmp_tp = array_values($tmp_tp);
                    }
                    $data_budrest[] = $tf;
                }
                $index = 0;
                for ($z = 0; $z < $bud_rest->target_face; $z++) {
                    $brs = $bud_rest_start;
                    for ($y = 0; $y < count($data_budrest); $y++) {
                        if (!isset($schedules[$index]))
                            break;
                        ArcheryEventQualificationScheduleFullDay::where("id", $schedules[$index]->id)->update([
                            "bud_rest_number" => $brs,
                            "target_face" => $data_budrest[$y][$z]
                        ]);
                        $count = $count + 1;
                        $brs = $brs + 1;
                        $index++;
                    }
                }
            }
            if ($count > 0) {
                // $download = BudRest::downloadQualificationScoreSheet($event_category_detail,true);
                // if(count($download["member_not_have_budrest"])){
                //     echo "member_not_have_budrest : ";
                //     print_r($download["member_not_have_budrest"]);
                // }
            }
            echo "----------------- FINISH " . $event_category_detail . " [" . $count . " data] -----------------\n\n";
        }
    }
}
