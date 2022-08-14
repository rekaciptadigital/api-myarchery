<?php

namespace App\Imports;

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
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class ParticipantImport implements ToModel, WithValidation, WithBatchInserts
{
    public function model(array $row)
    {
        $user = User::where("email", $row[1])->first();
        if (!$user) {
            $user = new User;
            $user->name = $row[0];
            $user->email = $row[1];
            $user->password = Hash::make(str_random(6));
            $user->phone_number = $row[2];
            $user->gender = $row[3];
            $user->save();
        }

        $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $row[4])->first();
        if (!$qualification_time) {
            throw new BLoCException('event belum bisa di daftar');
        }

        $category = ArcheryEventCategoryDetail::find($row[4]);

        $event = ArcheryEvent::find($category->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        // cek apakah user telah pernah mendaftar di categori tersebut
        $isExist = ArcheryEventParticipant::where('event_category_id', $category->id)
            ->where('user_id', $user->id)->get();
        if ($isExist->count() > 0) {
            foreach ($isExist as $ie) {
                if ($ie->status == 1) {
                    throw new BLoCException("event dengan kategori ini sudah diikuti oleh " . $user->email);
                }
            }
        }

        // insert data participant
        $participant = ArcheryEventParticipant::create([
            'club_id' => 0,
            'user_id' => $user->id,
            'status' => 1,
            'event_id' => $event->id,
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
            'event_category_id' => $category->id,
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

        ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($category->id, $user->gender), $participant->id);
        ArcheryEventParticipantMemberNumber::saveMemberNumber(ArcheryEventParticipantMemberNumber::makePrefix($event->id, $user->gender), $user->id, $event->id);
        $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
        Redis::hset($key, $category->id, $category->id);
        ArcheryEventQualificationScheduleFullDay::create([
            'qalification_time_id' => $qualification_time->id,
            'participant_member_id' => $member->id,
        ]);
        ParticipantMemberTeam::saveParticipantMemberTeam($category->id, $participant->id, $member->id, $category->category_team);

        return $participant;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            "0" => "required|string",
            '1' => "required|email:rfc,dns",
            "2" => "required|numeric",
            "3" => "required|in:male,female",
            '4' => 'exists:archery_event_category_details,id'
        ];
    }
    public function customValidationAttributes()
    {
        return [
            "0" => "name",
            '1' => 'email',
            "2" => "phone number",
            "3" => "gender",
            "4" => "category_id"
        ];
    }
}
