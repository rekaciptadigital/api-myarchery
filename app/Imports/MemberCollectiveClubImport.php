<?php

namespace App\Imports;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ArcherySeriesUserPoint;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Str;

class MemberCollectiveClubImport implements ToCollection, WithHeadingRow
{
    protected $error_message = [];
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $list_errors = [];
        foreach ($collection as $key => $c) {
            $gender = $c["gender"];
            $email = $c["email"];
            $name = $c["nama"];
            $date_of_birth = $c["tanggal_lahir"];
            $category_id = $c["kategori_id"];
            $club_id = $c["club_id"];

            if (
                !$gender
                && !$email
                && !$name
                && !$date_of_birth
                && !$category_id
                && !$club_id
            ) {
                continue;
            }

            $validator = Validator::make($c->toArray(), [
                'email' => 'required|email',
                'nama' => "required",
                "gender" => "in:male,female",
                "kategori_id" => "required|exists:archery_event_category_details,id",
                "tanggal_lahir" => "required",
                "club_id" => "required",
            ]);

            if ($validator->fails()) {
                $row = $key;
                $message = $validator->errors();
                $list_errors[] = [
                    "message" => $message,
                    "row" => $row + 1
                ];
                continue;
            }

            $user_new = User::where("email", $email)->first();
            if (!$user_new) {
                $user_new = new User;
                $user_new->gender = $gender;
                $user_new->name = $name;
                $user_new->password = Hash::make("12345678");
                $user_new->email = $email;
                $user_new->date_of_birth = Date::excelToDateTimeObject($date_of_birth)->format("Y-m-d");
                $user_new->save();
            }

            if ($club_id != 0) {
                $club = ArcheryClub::find($club_id);
                if (!$club) {
                    $row = $key + 1;
                    throw new BLoCException("club not found on row " . $row);
                }
            }

            $category = ArcheryEventCategoryDetail::select(
                "archery_event_category_details.*",
                "archery_master_team_categories.type as type_team",
                "archery_master_age_categories.min_age as min_age_master",
                "archery_master_age_categories.max_age as max_age_master",
                "archery_master_age_categories.min_date_of_birth as min_date_of_birth_master",
                "archery_master_age_categories.max_date_of_birth as max_date_of_birth_master"
            )
                ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
                ->where("archery_event_category_details.id", $category_id)
                ->first();

            if (!$category) {
                throw new BLoCException("category not found");
            }

            if (strtolower($category->category_team) != "individual") {
                throw new BLoCException("category must be individual type");
            }

            // start : cek category umur
            if ($category->is_age == 1) {
                if ($category->max_age_master > 0) {
                    if ($user_new->age > $category->max_age_master) {
                        throw new BLoCException("age invalid");
                    }
                }

                if ($category->min_age_master > 0) {
                    if ($user_new->age < $category->min_age_master) {
                        throw new BLoCException("age invalid");
                    }
                }
            } else {
                // cek jika ada persyaratan tanggal minimal kelahiran
                if ($category->min_date_of_birth_master != null) {
                    if (strtotime($user_new->date_of_birth) < strtotime($category->min_date_of_birth_master)) {
                        throw new BLoCException("tidak memenuhi syarat kelahiran, syarat kelahiran minimal adalah " . date("Y-m-d", strtotime($category->min_date_of_birth_master)));
                    }
                }

                if ($category->max_date_of_birth_master != null) {
                    if (strtotime($user_new->date_of_birth) > strtotime($category->max_date_of_birth_master)) {
                        throw new BLoCException("tidak memenuhi syarat kelahiran, syarat kelahiran maksimal adalah " . date("Y-m-d", strtotime($category->max_date_of_birth_master)));
                    }
                }
            }



            $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category->id)->first();
            if (!$qualification_time) {
                throw new BLoCException('event belum bisa di daftar');
            }

            $event = ArcheryEvent::find($category->event_id);
            if (!$event) {
                throw new BLoCException("event tidak ditemukan");
            }
            if ($event->with_contingent != 0) {
                throw new BLoCException("event must be with_contingent_format == 0");
            }

            // cek apakah user telah pernah mendaftar di categori tersebut
            $isExist = ArcheryEventParticipant::where('event_category_id', $category->id)
                ->where('user_id', $user_new->id)
                ->where("status", 1)
                ->first();

            if ($isExist) {
                $row = $key + 1;
                throw new BLoCException("event dengan kategori ini sudah di ikuti oleh user dengan email " . $c["email"] . " pada row " . $row);
            }

            // insert data participant
            $participant = new ArcheryEventParticipant();
            $participant->club_id = $club_id;
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
            $participant->city_id = 0;
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
            ArcheryEventQualificationScheduleFullDay::create([
                'qalification_time_id' => $qualification_time->id,
                'participant_member_id' => $member->id,
            ]);
            ArcherySeriesUserPoint::setAutoUserMemberCategory($category->event_id, $user_new->id);
        }

        if (count($list_errors) > 0) {
            throw new BLoCException("failed", $list_errors);
        }
    }
}
