<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventIdcardTemplate;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventOfficial;
use App\Models\User;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ChildrenClassificationMembers;
use App\Models\City;
use App\Models\CityCountry;
use App\Models\Country;
use App\Models\ProvinceCountry;
use App\Models\Provinces;
use Illuminate\Support\Facades\Auth;

class BulkDownloadIdCardByCategoryIdV2 extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $category_id = $parameters->get("category_id");
        $event_id = $parameters->get('event_id');
        $type = $parameters->get("type"); // 1 untuk peserta 2 untuk official

        $archery_event = ArcheryEvent::find($event_id);
        if (!$archery_event) {
            throw new BLoCException("event tidak tersedia tersedia");
        }

        if ($archery_event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $final_doc = [];

        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $event_id)->first();
        if (!$idcard_event) {
            throw new BLoCException("ID card bantalan belum di set, silahkan konfigurasi di menu ID card");
        }

        $html_template = base64_decode($idcard_event->html_template);
        $background = $idcard_event->background;
        $logo = !empty($idcard_event->logo_event) ? $idcard_event->logo_event : "https://i.ibb.co/pXx14Zr/logo-email-archery.png";
        $location_and_date_event = $archery_event->location_date_event;

        if ($type == 1) {
            $status = "Peserta";
            $final_doc = $this->generateArrayParticipant($archery_event, $category_id, $background, $html_template, $logo, $status, $type);
        } elseif ($type == 2) {
            $status = "Official";
            $final_doc = $this->generateArrayOfficial($event_id, $location_and_date_event, $background, $html_template, $logo, $status, $type);
        }

        $editor_data = json_decode($idcard_event->editor_data);
        $paper_size = $editor_data->paperSize;
        $orientation = array_key_exists("orientation", $editor_data) ? $editor_data->orientation : "P";
        $category_file = $type == 1 ? str_replace(' ', '', $final_doc['label']) : $archery_event->event_name;
        $file_name = $type == 1 ? "asset/idcard/idcard_" . $category_file . "_" . $final_doc["category_id"] . ".pdf" : "asset/idcard/idcard_" . $category_file  . ".pdf";
        PdfLibrary::setArrayDoc($final_doc['doc'])->setFileName($file_name)->savePdf(null, $paper_size, $orientation);
        return [
            "file_name" => env('APP_HOSTNAME') . $file_name,
            // "file_base_64" => env('APP_HOSTNAME') . $generate_idcard,
        ];
    }

    protected function validation($parameters)
    {
        $validator = [
            'event_id' => 'required',
            'type' => 'required'
        ];
        if ($parameters->get("type") == 1) {
            $validator["category_id"] = 'required';
        }

        return $validator;
    }

    private function generateArrayParticipant(ArcheryEvent $event, $category_id, $background, $html_template, $logo, $status, $type)
    {
        $category = ArcheryEventCategoryDetail::where("event_id", $event->id)->where("id", $category_id)->first();

        if (!$category) {
            throw new BLoCException("category not found");
        }

        $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($category->id);

        $participants = ArcheryEventParticipant::where("event_category_id", $category_id)->where("status", 1)->get();
        if ($participants->count() == 0) {
            throw new BLoCException("tidak ada partisipan");
        }

        $final_doc = [];

        foreach ($participants as $participant) {
            $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
            if (!$member) {
                throw new BLoCException("tidak ada data tersedia");
            }

            $user = User::find($member->user_id);
            if (!$user) {
                throw new BLoCException("user not found");
            }

            if ($user->gender == "male") {
                $gender = "Laki-Laki";
            } elseif ($user->gender == "female") {
                $gender = "Perempuan";
            } else {
                $gender = "";
            }

            $qr_code_data = $event->id . " " . $type . "-" . $member->id;
            $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $member->id)->first();
            $budrest_number = "";
            if ($schedule && $schedule->bud_rest_number != 0) {
                $budrest_number = $schedule->bud_rest_number . $schedule->target_face;
            }

            $tag_ranked = "";

            if ($event->parent_classification == 1) { // jika mewakili club
                $club = ArcheryClub::find($participant->club_id);
                if ($club) {
                    $tag_ranked = $club->name;
                }
            }

            if ($event->parent_classification == 2) { // jika mewakili negara
                $country = Country::find($participant->classification_country_id);
                if ($country) {
                    $tag_ranked = $country->name;
                }
            }

            if ($event->parent_classification == 3) { // jika mewakili provinsi
                if ($event->classification_country_id == 102) {
                    $province = Provinces::find($participant->classification_province_id);
                } else {
                    $province = ProvinceCountry::find($participant->classification_province_id);
                }

                if ($province) {
                    $tag_ranked = $province->name;
                }
            }

            if ($event->parent_classification == 4) { // jika mewakili kota
                if ($event->classification_country_id == 102) {
                    $city = City::find($participant->classification_city_id);
                } else {
                    $city = CityCountry::find($participant->classification_city_id);
                }

                if ($city) {
                    $tag_ranked = $city->name;
                }
            }

            if ($event->parent_classification > 5) { // jika berasal dari settingan admin
                $children_classification_member = ChildrenClassificationMembers::find($participant->children_classification_id);
                if ($children_classification_member) {
                    $tag_ranked = $children_classification_member->title;
                }
            }

            $avatar = !empty($user->avatar) ? $user->avatar : "https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png";

            $final_doc['doc'][] = str_replace(
                ['{%player_name%}', '{%avatar%}', '{%category%}', '{%club_member%}', "{%background%}", '{%logo%}', '{%location_and_date%}', '{%certificate_verify_url%}', '{%status_event%}', '{%budrest_number%}', '{%gender%}'],
                [$user->name, $avatar, $categoryLabel, $tag_ranked, $background, $logo, $event->location_and_date_event, $qr_code_data, $status, $event->status, $budrest_number, $gender],
                $html_template
            );
        }
        $final_doc["label"] = $categoryLabel;
        $final_doc["category_id"] = $category->id;
        return $final_doc;
    }

    private function generateArrayOfficial($event_id, $location_and_date_event, $background, $html_template, $logo, $status, $type)
    {
        $official = ArcheryEventOfficial::select("archery_event_official.*")
            ->join("archery_event_official_detail", "archery_event_official_detail.id", "=", "archery_event_official.event_official_detail_id")
            ->where("archery_event_official.status", 1)
            ->where("archery_event_official_detail.event_id", $event_id)
            ->get();

        if ($official->count() == 0) {
            throw new BLoCException("tidak ada data official");
        }

        foreach ($official as $o) {
            $user = User::find($o->user_id);
            if (!$user) {
                throw new BLoCException("user not found");
            }

            $gender = "";
            if ($user->gender != null) {
                if ($user->gender == "male") {
                    $gender = "Laki-Laki";
                } else {
                    $gender = "Perempuan";
                }
            }

            $data_qr = $event_id . " " . $type . "-" . $o->id;

            $club = ArcheryClub::find($o->club_id);
            if (!$club) {
                $club = '';
            } else {
                $club = $club->name;
            }

            $avatar = !empty($user->avatar) ? $user->avatar : "https://i0.wp.com/eikongroup.co.uk/wp-content/uploads/2017/04/Blank-avatar.png?ssl=1";

            $final_doc['doc'][] = str_replace(
                ['{%category%}', '{%player_name%}', '{%avatar%}', '{%club_member%}', "{%background%}", '{%logo%}', '{%location_and_date%}', '{%certificate_verify_url%}', '{%status_event%}', '{%gender%}'],
                ["", $user->name, $avatar, $club, $background, $logo, $location_and_date_event, $data_qr, $status, $gender],
                $html_template
            );
        }
        return $final_doc;
    }
}
