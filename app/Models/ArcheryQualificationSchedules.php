<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventQualification;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventQualificationDetail;
use DateTime;
use DateInterval;
use DatePeriod;

class ArcheryQualificationSchedules extends Model
{
    protected function List($event_id, $participant_member_id = 0){
        $event = ArcheryEvent::find($event_id);
        $start_time = new DateTime($event->qualification_start_datetime);
        $end_time = new DateTime($event->qualification_end_datetime);
        $qualification = ArcheryEventQualification::where("event_id", $event_id)->get();
        $schedule = array();
        $my_schedule = [];
        $my_schedule_session = [];
        if($participant_member_id){
            $my_schedule_booking = ArcheryQualificationSchedules::where("participant_member_id",$participant_member_id)->get();
            if($my_schedule_booking && count($my_schedule_booking) > 0){
                foreach ($my_schedule_booking as $key => $msb) {
                    $my_schedule[$msb->date][$msb->qualification_detail_id][$msb->id] = array("my_schedule_id"=>$msb->id);
                }
            }
        }
        foreach ($qualification as $key => $value) {
            $session = ArcheryEventQualificationDetail::where("event_qualification_id",$value->id)->orderBy(DB::raw("DATE_FORMAT(start_time,'%m-%s')"),"ASC")->get();
            
            $sess = [];
            foreach ($session as $k => $s) {
                $sess[] = array(
                    "id" => $s->id,
                    "event_qualification_id" => $s->event_qualification_id,
                    "start_time" => $s->start_time,
                    "end_time" => $s->end_time,
                    "quota" => $s->quota,
                );
            }
        
            $schedule[$value->day_id] = array(
                "id" => $value->id,
                "day_label" => $value->day_label,
                "session" => $sess
            );
        }

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start_time, $interval, $end_time->modify('+1 day'));
        $schedule_on_periode = [];
        $disable_date = [];
        foreach ($period as $dt) {
            $day = \strtolower($dt->format("l"));
            $date = $dt->format("Y-m-d");
            $tmp_schedule = [];
            if(isset($schedule[$day])){
                $tmp_schedule = $schedule[$day];
                $tmp_schedule["day_id"] = $day;
                $tmp_schedule["date"] = $date;
                $tmp_schedule["date_label"] = date_format(date_create($date),"d M Y");
                // $posts->map(function ($post) {
                $full = 1;
                for ($i=0; $i < count($tmp_schedule["session"]); $i++) { 
                    $my_session = 0;
                    if(isset($my_schedule[$date][$tmp_schedule["session"][$i]["id"]])){
                        foreach ($my_schedule[$date][$tmp_schedule["session"][$i]["id"]] as $msKey => $ms) {
                            $my_schedule_session[] = array("date"=>$date,
                                                        "date_label"=>date_format(date_create($date),"d M Y"),
                                                        "my_schedule_id" => $ms["my_schedule_id"],
                                                        "session" => $tmp_schedule["session"][$i],
                                                        "day_id" => $day,
                                                        "day_label" => $schedule[$day]["day_label"],
                                                    );
                            $my_session = 1;
                        }
                    }
                    $total_schedule_booking = ArcheryQualificationSchedules::where("qualification_detail_id",$tmp_schedule["session"][$i]["id"])
                                    ->where("date",$date)
                                    ->count();
                    if($total_schedule_booking < $tmp_schedule["session"][$i]){
                        $full = 0;
                    }
                    $tmp_schedule["session"][$i]["total_booking"] = $total_schedule_booking;    
                    $tmp_schedule["session"][$i]["my_session"] = $my_session;    
                }
                if($full == 1){
                    $disable_date[] = $date;
                }
                // $tmp_schedule["session"]["total_booking"] = $total_schedule_booking;
                $schedule_on_periode[] = $tmp_schedule;
            }
        }

        $output = array("list"=>$schedule_on_periode,"event"=>$event);
        $output["disable_date"] = $disable_date;
        if($participant_member_id){
            $output["my_schedule"] = $my_schedule_session;
        }
        return $output;
    }

    protected function memberScheduleDetail($participant_member_id){
        $my_schedule = [];
        $my_schedule_booking = ArcheryQualificationSchedules::where("participant_member_id",$participant_member_id)->get();
        if($my_schedule_booking && count($my_schedule_booking) > 0){
            foreach ($my_schedule_booking as $key => $msb) {
                $session = ArcheryEventQualificationDetail::find($msb->qualification_detail_id);
                $qualification = ArcheryEventQualification::find($session->event_qualification_id);
                $session->day = $qualification->day_label;
                $date = date_create($msb->date);
                $msb->session = $session;
                $msb->date_label = \date_format($date,"d M Y");
                $my_schedule[] = $msb;
            }
        }
        return $my_schedule;
    }
}
