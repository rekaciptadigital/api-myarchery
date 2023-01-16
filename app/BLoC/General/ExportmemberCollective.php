<?php

namespace App\BLoC\General;

use App\Exports\MemberContingentExport;
use App\Libraries\Upload;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\City;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;
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
        $event_id = $parameters->get("event_id");
        $city_id = $parameters->get("city_id");
        $responsible_name = $parameters->get("responsible_name");
        $responsible_phone_number = $parameters->get("responsible_phone_number");
        $responsible_email = $parameters->get("responsible_email");
        $list_members = $parameters->get("list_members");


        $chec_format_phone_number = preg_match("^(\+62|62|0)8[1-9][0-9]{6,9}$^", $responsible_phone_number);
        if ($chec_format_phone_number != 1) {
            throw new BLoCException("invalid phone number format");
        }

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
        foreach ($list_members as $member) {
            $email = $member["email"];
            $phone_number = $member["phone_number"];
            $name = $member["name"];
            $category_id = $member["category_id"];
            $gender = $member["gender"];
            $date_of_birth = date("Y-m-d", strtotime($member["date_of_birth"]));
            $ktp_kk = $member["ktp_kk"];
            $no_recomendation_later = $member["no_recomendation_later"];
            $binaan_later = $member["binaan_later"];

            $chec_format_phone_number = preg_match("^(\+62|62|0)8[1-9][0-9]{6,9}$^", $phone_number);
            if ($chec_format_phone_number != 1) {
                throw new BLoCException("invalid phone number format");
            }

            // upload ktp_kk dan surat binaan
            $url_ktp_kk = Upload::setPath("asset/ktp_kk/")->setFileName("ktp_kk_" . $email)->setBase64($ktp_kk)->save();
            $url_binaan_later = Upload::setPath("asset/binaan_later/")->setFileName("binaan_later_" . $email)->setBase64($binaan_later)->save();

            // replace data array
            $member["ktp_kk"] = $url_ktp_kk;
            $member["binaan_later"] = $url_binaan_later;

            $category = ArcheryEventCategoryDetail::select(
                "archery_event_category_details.*",
                "archery_master_age_categories.min_age as min_age_category",
                "archery_master_age_categories.max_age as max_age_category"
            )
                ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
                ->where("archery_event_category_details.id", $category_id)
                ->first();

            if ($category->event_id != $event_id) {
                throw new BLoCException("category invalid");
            }

            $today = Carbon::today('Asia/jakarta');
            $age = $today->diffInYears($date_of_birth);



            if ($age > $category->max_age_category || $age < $category->min_age_category) {
                throw new BLoCException("age invalid");
            }

            $member["city_id"] = $city_id;
            $member["responsible_name"] = $responsible_name;
            $member["responsible_phone_number"] = $responsible_phone_number;
            $member["responsible_email"] = $responsible_email;

            $new_list_member[] = $member;
        }

        $file_name = "member_collective_" . $city_id . "_.xlsx";
        $final_doc = '/member_collective/' . $event_id . '/' . $file_name;
        $excel = new MemberContingentExport($new_list_member);
        Excel::store($excel, $final_doc, 'public');
        $destinationPath = Storage::url($final_doc);
        $file_path = env('STOREG_PUBLIC_DOMAIN') . $destinationPath;
        return $file_path;
    }

    protected function validation($parameters)
    {
        $rules = [];
        $rules["event_id"] = "required|exists:archery_events,id";
        $rules["responsible_name"] = "required|string";
        $rules["city_id"] = "required|exists:cities,id";
        $rules["responsible_email"] = "required|email";
        $rules["responsible_phone_number"] = "required|numeric";
        $rules["list_members"] = "required|array";
        $rules["list_members.*.category_id"] = "required|exists:archery_event_category_details,id";
        $rules["list_members.*.email"] = "required|email";
        $rules["list_members.*.name"] = "required";
        $rules["list_members.*.phone_number"] = "required|numeric";
        $rules["list_members.*.gender"] = "required|in:male,female";
        $rules["list_members.*.date_of_birth"] = "required";
        $rules["list_members.*.ktp_kk"] = "required";
        $rules["list_members.*.no_recomendation_later"] = "required";
        $rules["list_members.*.binaan_later"] = "required";

        return $rules;
    }
}
