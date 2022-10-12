<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Hash;

class InsertParticipantByAdmin extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        // $category_id = $parameters->get("category_id");
        $emails = $parameters->get("emails");

        $object = $parameters->get("object");

        foreach ($object as $key => $o) {
            $user_new = User::where("email", $o["email"])->first();
            if (!$user_new) {
                $user_new = new User;
                $user_new->gender = "male";
                $user_new->name = $o["name"];
                $user_new->password = Hash::make("12345678");
                $user_new->email = $o["email"];
                $user_new->phone_number = $o["phone_number"];
                $user_new->save();
            }

            $category = ArcheryEventCategoryDetail::find($o["category_id"]);
            if (!$category) {
                throw new BLoCException("category not found");
            }

            $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category->id)->first();
            if (!$qualification_time) {
                throw new BLoCException('event belum bisa di daftar');
            }

            $event = ArcheryEvent::find($category->event_id);
            if (!$event) {
                throw new BLoCException("event tidak ditemukan");
            }

            // cek apakah user telah pernah mendaftar di categori tersebut
            $isExist = ArcheryEventParticipant::where('event_category_id', $category->id)
                ->where('user_id', $user_new->id)->get();
            if ($isExist->count() > 0) {
                foreach ($isExist as $ie) {
                    if ($ie->status == 1) {
                        throw new BLoCException("event dengan kategori ini sudah di ikuti oleh user dengan email " . $o["email"]);
                    }
                }
            }

            // insert data participant
            $participant = new ArcheryEventParticipant();
            $participant->club_id = 0;
            $participant->user_id = $user_new->id;
            $participant->status = 1;
            $participant->event_id = $event->id;
            $participant->name = $user_new->name;
            $participant->type = $category->category_team;
            $participant->email = $user_new->email;
            $participant->phone_number = $user_new->phone_number;
            $participant->age = $user_new->age;
            $participant->gender = $user_new->gender;
            $participant->team_category_id = $category->team_category_id;
            $participant->age_category_id = $category->age_category_id;
            $participant->competition_category_id = $category->competition_category_id;
            $participant->distance_id = $category->distance_id;
            $participant->transaction_log_id = 0;
            $participant->unique_id = Str::uuid();
            $participant->event_category_id = $category->id;
            $participant->register_by = 2;
            $participant->save();

            // insert ke archery_event_participant_member
            $member = ArcheryEventParticipantMember::create([
                "archery_event_participant_id" => $participant->id,
                "name" => $user_new->name,
                "gender" => $user_new->gender,
                "birthdate" => $user_new->date_of_birth,
                "age" => $user_new->age,
                "team_category_id" => $category->team_category_id,
                "user_id" => $user_new->id
            ]);

            ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($category->id, $user_new->gender), $participant->id);
            ArcheryEventParticipantMemberNumber::saveMemberNumber(ArcheryEventParticipantMemberNumber::makePrefix($event->id, $user_new->gender), $user_new->id, $event->id);
            $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
            Redis::hset($key, $category->id, $category->id);
            ArcheryEventQualificationScheduleFullDay::create([
                'qalification_time_id' => $qualification_time->id,
                'participant_member_id' => $member->id,
            ]);
            ParticipantMemberTeam::saveParticipantMemberTeam($category->id, $participant->id, $member->id, $category->category_team);
        }

        return "success";
    }

    private function getAge($birth_day, $date_check)
    {
        $birthDt = new DateTime($birth_day);
        $date = new DateTime($date_check);
        return [
            "y" => $date->diff($birthDt)->y,
            "m" => $date->diff($birthDt)->m,
            "d" => $date->diff($birthDt)->d
        ];
    }

    protected function validation($parameters)
    {
        return [
            // "category_id" => "required|integer",
            // "emails"    => "required|array|min:1|max:20",
            // "emails.*"  => "required|email|distinct",
            "object" => "required"
        ];
    }
}
