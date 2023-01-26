<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\City;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use Illuminate\Support\Str;
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

        $object = $parameters->get("object");

        foreach ($object as $key => $o) {
            $club_id = $o["club_id"];
            $city_id = $o["city_id"];

            if ($club_id > 0) {
                $club = ArcheryClub::find($club_id);
                if (!$club) {
                    throw new BLoCException("club not found");
                }
            }

            $user_new = User::where("email", $o["email"])->first();
            if (!$user_new) {
                $user_new = new User;
                $user_new->gender = $o["gender"];
                $user_new->name = $o["name"];
                $user_new->password = Hash::make("12345678");
                $user_new->email = $o["email"];
                $user_new->phone_number = $o["phone_number"];
                $user_new->save();
            }

            $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type as type_team")
                ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                ->where("archery_event_category_details.id", $o["category_id"])
                ->first();

            if (!$category) {
                throw new BLoCException("category not found");
            }

            if (strtolower($category->type_team) == "individual") {
                $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category->id)->first();
                if (!$qualification_time) {
                    throw new BLoCException('event belum bisa di daftar');
                }
            }

            $event = ArcheryEvent::find($category->event_id);
            if (!$event) {
                throw new BLoCException("event tidak ditemukan");
            }

            if ($city_id > 0) {
                $city = City::where("id", $city_id)->where("province_id", $event->province_id)->first();
                if (!$city) {
                    throw new BLoCException("city not found");
                }
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
            $participant = ArcheryEventParticipant::saveArcheryEventParticipant(
                $user_new,
                $category,
                "individual",
                0,
                Str::uuid(),
                null,
                null,
                1,
                $club_id,
                null,
                null,
                1,
                1,
                null,
                0,
                0,
                0,
                $city_id
            );

            if (strtolower($category->type_team) == "individual") {
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
            "object" => "required",
            "object.*.email" => "required",
            "object.*.name" => "required",
            "object.*.phone_number" => "required",
            "object.*.category_id" => "required|numeric|exists:archery_event_category_details,id",
            "object.*.gender" => "required|in:male,female",
            "object.*.club_id" => "required",
            "object.*.city_id" => "required",
        ];
    }
}
