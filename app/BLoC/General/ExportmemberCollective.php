<?php

namespace App\BLoC\General;

use App\Exports\MemberContingentExport;
use App\Libraries\Upload;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\City;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportmemberCollective extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();
        $event_id = $parameters->get("event_id");
        $city_id = $parameters->get("city_id");
        $list_members = $parameters->get("list_members");

        $event = ArcheryEvent::find($event_id);
        if ($event->with_contingent != 1) {
            throw new BLoCException("event must be with_contingent_format");
        }

        $province_id = $event->province_id;

        $city = City::find($city_id);
        if ($city->province_id != $province_id) {
            throw new BLoCException("province and city invalid");
        }

        $new_list_member = [];
        $total_price = 0;
        $list_email_and_category_object = [];
        foreach ($list_members as $key => $member) {
            $email = $member["email"];
            $phone_number = $member["phone_number"];
            $category_id = $member["category_id"];
            $date_of_birth = date("Y-m-d", strtotime($member["date_of_birth"]));
            $ktp_kk = $member["ktp_kk"];
            $gender = $member["gender"];

            // start : memastikan tidak ada email duplicate dengan insert satu object ke list untuk pengecekan di akhir
            foreach ($list_email_and_category_object as $key_lec => $lec) {
                $row = $key+1;
                if ($lec->email == $email && $lec->category_id == $category_id) {
                    throw new BLoCException("email duplikat pada form " . $row);
                }
            }
            $list_email_and_category_object[] = (object)[
                "email" => $email,
                "category_id" => $category_id
            ];
            // end : memastikan tidak ada email duplicate

            $chec_format_phone_number = preg_match("^(\+62|62|0)8[1-9][0-9]{6,9}$^", $phone_number);
            if ($chec_format_phone_number != 1) {
                throw new BLoCException("invalid phone number format for email " . $email);
            }

            // upload ktp_kk dan surat binaan
            $url_ktp_kk = Upload::setPath("asset/ktp_kk/")->setFileName("ktp_kk_" . $email)->setBase64($ktp_kk)->save();

            // replace data array
            $member["ktp_kk"] = $url_ktp_kk;

            $category = ArcheryEventCategoryDetail::select(
                "archery_event_category_details.*",
                "archery_master_age_categories.min_age as min_age_category_master_age",
                "archery_master_age_categories.max_age as max_age_category_master_age",
                "archery_master_age_categories.is_age",
                "archery_master_age_categories.min_date_of_birth as min_date_of_birth_master_age",
                "archery_master_age_categories.max_date_of_birth as max_date_of_birth_master_age"
            )
                ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
                ->where("archery_event_category_details.id", $category_id)
                ->where("archery_event_category_details.event_id", $event_id)
                ->first();

            if (!$category) {
                throw new BLoCException("category not found");
            }

            if (strtolower($category->category_team) != "individual") {
                throw new BLoCException("category must be individual type type");
            }

            $total_price += (int)$category->fee;

            $today = Carbon::today('Asia/jakarta');
            $age = $today->diffInYears($date_of_birth);

            $user = User::where("email", $email)->first();
            if ($user) {
                $check_participant = ArcheryEventParticipant::where("event_category_id", $category_id)
                    ->where("user_id", $user->id)
                    ->where("status", 1)
                    ->first();
                if ($check_participant) {
                    throw new BLoCException("user dengan email " . $email . " telah terdaftar di categori " . $category->label_category);
                }

                $gender = $user->gender;
                $age = $user->age;
                $date_of_birth = $user->date_of_birth;
            } 

            // start : cek category umur
            if ($category->is_age == 1) {
                if ($category->max_age_category_master_age > 0) {
                    if ($age > $category->max_age_category_master_age) {
                        throw new BLoCException("age invalid");
                    }
                }

                if ($category->min_age_category_master_age > 0) {
                    if ($age < $category->min_age_category_master_age) {
                        throw new BLoCException("age invalid");
                    }
                }
            } else {
                // cek jika ada persyaratan tanggal minimal kelahiran
                if ($category->min_date_of_birth_master_age != null) {
                    if (strtotime($date_of_birth) < strtotime($category->min_date_of_birth_master_age)) {
                        throw new BLoCException("tidak memenuhi syarat kelahiran, syarat kelahiran minimal adalah " . date("Y-m-d", strtotime($category->min_date_of_birth_master_age)));
                    }
                }

                if ($category->max_date_of_birth_master_age != null) {
                    if (strtotime($date_of_birth) > strtotime($category->max_date_of_birth_master_age)) {
                        throw new BLoCException("tidak memenuhi syarat kelahiran, syarat kelahiran maksimal adalah " . date("Y-m-d", strtotime($category->max_date_of_birth_master_age)));
                    }
                }
            }
            // End: Cek kategory umur

            if ($gender != $category->gender_category) {
                throw new BLoCException("gender invalid for email " . $email);
            }

            $member["city_id"] = $city_id;
            $member["city_label"] = $city->name;
            $member["category_label"] = $category->label_category;
            $member["responsible_name"] = $user_login->name;
            $member["responsible_phone_number"] = $user_login->phone_number;
            $member["responsible_email"] = $user_login->email;

            $new_list_member[] = $member;
        }

        $file_name = "member_collective_" . $city_id . "_.xlsx";
        $final_doc = '/member_collective/' . $event_id . '/' . $file_name;
        $excel = new MemberContingentExport($new_list_member);
        Excel::store($excel, $final_doc, 'public');
        $destinationPath = Storage::url($final_doc);
        $file_path = env('STOREG_PUBLIC_DOMAIN') . $destinationPath;

        // membuat table di db untuk penampungan data eksel yang telah di submit oleh user
        // isi field nya berupa user_id event_id city_id filepahth excell 
        // mengubah nama file excell
        // insertkan ke table tersebut setelah ngisi form


        return [
            "file_excell" => $file_path,
            "total_price" => $total_price
        ];
    }

    protected function validation($parameters)
    {
        $rules = [];
        $rules["event_id"] = "required|exists:archery_events,id";
        $rules["city_id"] = "required|exists:cities,id";
        $rules["list_members"] = "required|array";
        $rules["list_members.*.category_id"] = "required|exists:archery_event_category_details,id";
        $rules["list_members.*.email"] = "required|email";
        $rules["list_members.*.name"] = "required";
        $rules["list_members.*.phone_number"] = "required|numeric";
        $rules["list_members.*.gender"] = "required|in:male,female";
        $rules["list_members.*.date_of_birth"] = "required";
        $rules["list_members.*.ktp_kk"] = "required";

        return $rules;
    }
}
