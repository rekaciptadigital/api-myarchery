<?php

namespace App\Exports\Sheets;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventMasterCategoryCode;
use App\Models\ArcheryEvent;
use App\Models\User;
use App\Models\City;
use App\Models\Provinces;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryUserAthleteCode;
use App\Models\CityCountry;
use App\Models\Country;
use DateTime;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ArcheryEventParticipantSheet implements FromView, WithColumnWidths, WithHeadings, WithColumnFormatting
{
    protected $event_id;

    function __construct($event_id)
    {
        $this->event_id = $event_id;
    }

    public function view(): View
    {
        $event_id = $this->event_id;

        $event = ArcheryEvent::find($event_id);

        $parent_classifification_id = $event->parent_classification;

        $data = ArcheryEventParticipant::select(
            'archery_events.event_start_datetime',
            'archery_event_participants.event_category_id',
            'archery_event_participants.id',
            'archery_event_participants.user_id',
            'archery_event_participants.created_at',
            'archery_event_participant_members.is_series',
            'archery_event_participants.email',
            'archery_event_participants.phone_number',
            'archery_event_participants.team_category_id',
            'archery_event_participants.gender',
            'event_name',
            "archery_event_participants.club_id as club_id",
            "archery_clubs.name as club_name",
            "archery_event_participants.classification_country_id as country_id",
            "countries.name as country_name",
            "archery_event_participants.classification_province_id as province_id",
            $event->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
            "archery_event_participants.city_id",
            $event->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
            "archery_event_participants.children_classification_id",
            "children_classification_members.title as children_classification_members_name"
        )
            ->leftJoin("archery_events", "archery_events.id", "=", "archery_event_participants.event_id")
            ->leftJoin("archery_event_participant_members", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id");
        // jika mewakili club
        $data = $data->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");


        // jika mewakili negara
        $data = $data->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");


        // jika mewakili provinsi
        if ($event->classification_country_id == 102) {
            $data = $data->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
        } else {
            $data = $data->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
        }

        // jika mewakili kota
        if ($event->classification_country_id == 102) {
            $data = $data->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
        } else {
            $data = $data->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
        }

        // jika berasal dari settingan admin
        $data = $data->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

        $data = $data->where('archery_event_participants.event_id', $event_id)
            ->where("archery_event_participants.status", 1)
            ->get();

        $event =  ArcheryEvent::find($event_id);
        $time_stamp_event_start = strtotime($event->event_start_datetime);

        if ($data->isEmpty()) {
            throw new BLoCException("data tidak ditemukan");
        }

        $export_data = [];

        foreach ($data as $key => $value) {
            $category = ArcheryEventCategoryDetail::find($value->event_category_id);
            $category_label = ArcheryEventCategoryDetail::getCategoryLabelComplete($value->event_category_id);
            $category_code = ArcheryEventMasterCategoryCode::where("age_category_id", $category->age_category_id)
                ->where("distance_category_id", $category->distance_id)
                ->where("competition_category_id", $category->competition_category_id)
                ->where("team_category_id", $category->team_category_id)
                ->first();
            $user = User::select(
                'name',
                "phone_number",
                'address_province_id',
                'verify_status',
                'address_city_id',
                'address',
                'date_of_birth',
                'ktp_kk',
                'gender',
                'selfie_ktp_kk',
                'place_of_birth',
                "is_wna",
                "passport_number",
                "country_id",
                "city_of_country_id",
                'nik',
                DB::RAW("TIMESTAMPDIFF(YEAR, date_of_birth, $time_stamp_event_start) AS age")
            )->where('id', $value->user_id)->first();
            $athlete_code = ArcheryUserAthleteCode::getAthleteCode($value->user_id, "perpani");
            $city = City::find($user["address_city_id"]);
            $province = Provinces::find($user["address_province_id"]);
            $country = Country::find($user->country_id);
            $city_country = CityCountry::find($user->city_of_country_id);
            if (!empty($user['date_of_birth']))
                $age = $this->getAge($user['date_of_birth'], $value->event_start_datetime);
            $export_data[] = [
                'category_code' => $category_code ? $category_code->code : "",
                'athlete_code' => $athlete_code ? $athlete_code : '-',
                'timestamp' => $value->created_at,
                'is_series' => $value->is_series,
                'verify_status' => $user["verify_status"],
                'email' => $value->email,
                'full_name' => $user["name"],
                'gender' => $user->gender,
                'address' => $user["address"],
                'date_of_birth' => $user['date_of_birth'] . ', ' . $user["place_of_birth"],
                'age' => !empty($user['date_of_birth']) ? $age["y"] . " tahun " . $age["m"] . " bulan " . $age["d"] . " hari"  : '-',
                'phone_number' => $user->phone_number,
                'gender' => $user->gender,
                'province' => $province ? $province->name : "",
                'city' => $city ? $city->name : "",
                'category' => $category_label,
                'nik' => $user['nik'] ? "'" . $user['nik'] : '-',
                'nationality' => $user->is_wna == 1 ? "Asing" : "Indonesia",
                "country" => $country ? $country->name : "-",
                "city_of_country" => $city_country ? $city_country->name : "-",
                "passport_number" => $user->passport_number,
                "club_id" => $value->club_id,
                "club_name" => $value->club_name,
                "country_id" => $value->country_id,
                "country_name" => $value->country_name,
                "province_id" => $value->province_id,
                "province_name" => $value->province_name,
                "city_id" => $value->city_id,
                "city_name" => $value->city_name,
                "children_classification_id" => $value->children_classification_id,
                "children_classification_members_name" => $value->children_classification_members_name,
                "parent_classification_type" => $parent_classifification_id,
            ];
        }

        $event_name = strtoupper($data[0]['event_name']);
        $event_start_date = date("Y/m/d", strtotime($data[0]['event_start_datetime']));
        return view('reports.participant_event', [
            'datas' => $export_data,
            'event_name' => $event_name,
            'event_start_date' => $event_start_date,
        ]);
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

    public function headings(): array
    {
        return [
            'A' => 200,
            'B' => 200,
            'C' => 200
        ];
    }

    public function columnFormats(): array
    {
        return [
            'Q' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,
            'C' => 20,
            'D' => 30,
            'E' => 30,
            'F' => 20,
            'G' => 30,
            'H' => 30,
            'I' => 25,
            'J' => 20,
            'K' => 30,
            'L' => 30,
            'M' => 25,
            'N' => 30,
            'O' => 30,
            'P' => 20,
            'Q' => 30,
            'R' => 30,
            'S' => 30,
            'T' => 30,
            'U' => 30,
        ];
    }
}
