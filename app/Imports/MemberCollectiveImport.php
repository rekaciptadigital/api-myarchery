<?php

namespace App\Imports;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\City;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class MemberCollectiveImport implements ToCollection, WithHeadingRow
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
            $phone_number = $c["no_hp"];
            $date_of_birth = $c["tanggal_lahir"];
            $category_id = $c["kategori_id"];
            $city_id = $c["kota_id"];
            $email_penanggung_jawab = $c["email_penanggung_jawab"];

            if (
                !$gender
                && !$email
                && !$name
                && !$phone_number
                && !$date_of_birth
                && !$category_id
                && !$city_id
            ) {
                continue;
            }

            $validator = Validator::make($c->toArray(), [
                'email' => 'required|email',
                'nama' => "required",
                "gender" => "in:male,female",
                "no_hp" => "required",
                "kategori_id" => "required|exists:archery_event_category_details,id",
                "tanggal_lahir" => "required",
                "kota_id" => "required|exists:cities,id",
                "nama_penanggung_jawab" => "required",
                "no_hp_penanggung_jawab" => "required",
                "email_penanggung_jawab" => "required|email"
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
                $check_phone_number_exists = User::where("phone_number", $phone_number)->first();
                if ($check_phone_number_exists) {
                    throw new BLoCException("phone number already exists");
                }
                $user_new = new User;
                $user_new->gender = $gender;
                $user_new->name = $name;
                $user_new->password = Hash::make("12345678");
                $user_new->email = $email;
                $user_new->phone_number = $phone_number;
                $user_new->date_of_birth = Date::excelToDateTimeObject($date_of_birth)->format("Y-m-d");
                $user_new->save();
            }

            $chec_format_phone_number = preg_match("^(\+62|62|0)8[1-9][0-9]{6,9}$^", $user_new->phone_number);
            if ($chec_format_phone_number != 1) {
                throw new BLoCException("invalid phone number format");
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


            if (strtolower($category->type_team) == "individual") {
                $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category->id)->first();
                if (!$qualification_time) {
                    throw new BLoCException('event belum bisa di daftar');
                }
            }

            $penanggung_jawab = User::where("email", $email_penanggung_jawab)->first();
            if (!$penanggung_jawab) {
                throw new BLoCException("user penanggung jawab belum terdaftar");
            }

            $event = ArcheryEvent::find($category->event_id);
            if (!$event) {
                throw new BLoCException("event tidak ditemukan");
            }
            if ($event->with_contingent != 1) {
                throw new BLoCException("event must be with_contingent_format");
            }

            // $archery_event_official_detail = ArcheryEventOfficialDetail::where("event_id", $event->id)
            //     ->first();
            // if (!$archery_event_official_detail) {
            //     throw new BLoCException("official for this event not set");
            // }

            $city = City::find($city_id);
            if (!$city) {
                throw new BLoCException("Kota not found");
            }
            if ($city->province_id != $event->province_id) {
                throw new BLoCException("invalid city");
            }

            // $archery_event_official = ArcheryEventOfficial::where("event_official_detail_id", $archery_event_official_detail->id)->where("user_id", $penanggung_jawab->id)
            //     ->where("city_id", $city_id)
            //     ->first();
            // if (!$archery_event_official) {
            //     $archery_event_official = new ArcheryEventOfficial();
            //     $archery_event_official->user_id = $penanggung_jawab->id;
            //     $archery_event_official->status = 1;
            //     $archery_event_official->transaction_log_id = 0;
            //     $archery_event_official->event_official_detail_id = $archery_event_official_detail->id;
            //     $archery_event_official->city_id = $city_id;
            //     $archery_event_official->save();
            // }


            // cek apakah user telah pernah mendaftar di categori tersebut
            $isExist = ArcheryEventParticipant::where('event_category_id', $category->id)
                ->where('user_id', $user_new->id)
                ->where("status", 1)
                ->first();

            if ($isExist) {
                throw new BLoCException("event dengan kategori ini sudah di ikuti oleh user dengan email " . $c["email"]);
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
            $participant->city_id = $city_id;
            $participant->save();

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
                ArcheryEventQualificationScheduleFullDay::create([
                    'qalification_time_id' => $qualification_time->id,
                    'participant_member_id' => $member->id,
                ]);
                ParticipantMemberTeam::saveParticipantMemberTeam($category->id, $participant->id, $member->id, $category->category_team);
            }
        }

        if (count($list_errors) > 0) {
            throw new BLoCException("failed", $list_errors);
        }
    }
}
