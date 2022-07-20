<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class BulkInsertUserParticipant extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_id = $parameters->get("category_id");
        $admin_login = Auth::user();
        $array_names = $parameters->get("array_names");

        $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*")
            ->join("archery_events", "archery_events.id", "=", "archery_event_category_details.event_id")
            ->where("archery_event_category_details.id", $category_id)
            ->where("archery_events.admin_id", $admin_login->id)
            ->first();

        if (!$category) {
            throw new BLoCException("category not found");
        }

        $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category_id)->first();
        if (!$qualification_time) {
            throw new BLoCException('event belum bisa di daftar');
        }

        $gender = $category->gender_category;

        foreach ($array_names as $name) {
            $user = new User;
            $user->gender = $gender;
            $user->name = $name;
            $user->password = Hash::make("12345678");
            $user->save();

            $user->email = $user->id . "@myarchery.id";
            $user->save();

            // insert data participant
            $participant = ArcheryEventParticipant::create([
                'club_id' => 0,
                'user_id' => $user->id,
                'status' => 1,
                'event_id' => $category->event_id,
                'name' => $user->name,
                'type' => $category->category_team,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'age' => $user->age,
                'gender' => $user->gender,
                'team_category_id' => $category->team_category_id,
                'age_category_id' => $category->age_category_id,
                'competition_category_id' => $category->competition_category_id,
                'distance_id' => $category->distance_id,
                'transaction_log_id' => 0,
                'unique_id' => Str::uuid(),
                'event_category_id' => $category_id,
                "register_by" => 2
            ]);

            // insert ke archery_event_participant_member
            $member = ArcheryEventParticipantMember::create([
                "archery_event_participant_id" => $participant->id,
                "name" => $user->name,
                "gender" => $user->gender,
                "birthdate" => $user->date_of_birth,
                "age" => $user->age,
                "team_category_id" => $category->team_category_id,
                "user_id" => $user->id
            ]);

            ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($category_id, $user->gender), $participant->id);
            ArcheryEventParticipantMemberNumber::saveMemberNumber(ArcheryEventParticipantMemberNumber::makePrefix($category->event_id, $user->gender), $user->id, $category->event_id);
            $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
            Redis::hset($key, $category_id, $category_id);
            ArcheryEventQualificationScheduleFullDay::create([
                'qalification_time_id' => $qualification_time->id,
                'participant_member_id' => $member->id,
            ]);
            ParticipantMemberTeam::saveParticipantMemberTeam($category_id, $participant->id, $member->id, $category->category_team);
        }

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "category_id" => "required",
            "array_names" => "required"
        ];
    }
}
