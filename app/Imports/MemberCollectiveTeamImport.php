<?php

namespace App\Imports;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\City;
use App\Models\User;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;


class MemberCollectiveTeamImport implements ToCollection, WithHeadingRow
{
    protected $error_message = [];
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $list_errors = [];
        foreach ($collection as $key => $c) {
            $email_pendaftar = $c["email_pendaftar"];
            $category_team_id = $c["kategori_id"];
            $city_id = $c["kota_id"];
            $total_team = $c["total_team"];

            if (
                !$email_pendaftar
                && !$category_team_id
                && !$city_id
            ) {
                continue;
            }

            $validator = Validator::make($c->toArray(), [
                'email_pendaftar' => 'required|email',
                "kategori_id" => "required|exists:archery_event_category_details,id",
                "kota_id" => "required|exists:cities,id",
                "total_team" => "required|min:1"
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

            $category_team = ArcheryEventCategoryDetail::where("id", $category_team_id)
                ->first();

            if (!$category_team) {
                throw new BLoCException("category not found");
            }

            if (strtolower($category_team->category_team) == "individual") {
                throw new BLoCException("category must be team type");
            }

            $count_participant_team = ArcheryEventParticipant::where("event_id", $category_team->event_id)
                ->where("age_category_id", $category_team->age_category_id)
                ->where("competition_category_id", $category_team->competition_category_id)
                ->where("distance_id", $category_team->distance_id)
                ->where("team_category_id", $category_team->team_category_id)
                ->where("city_id", $city_id)
                ->get()
                ->count();

            $total_participant_team = $total_team + $count_participant_team;


            // validasi total peserta individu untuk pendaftaran beregu
            if ($category_team->team_category_id == "male_team" || $category_team->team_category_id == "female_team") {
                $team_category_id = $category_team->team_category_id == "male_team" ? "individu male" : "individu female";
                $count_participant_individu = ArcheryEventParticipant::where("event_id", $category_team->event_id)
                    ->where("age_category_id", $category_team->age_category_id)
                    ->where("competition_category_id", $category_team->competition_category_id)
                    ->where("distance_id", $category_team->distance_id)
                    ->where("team_category_id", $team_category_id)
                    ->where("city_id", $city_id)
                    ->get()
                    ->count();

                if ($count_participant_individu == 0) {
                    throw new BLoCException("participant individu not found");
                }

                $tmp = $count_participant_individu / 3;
                if ($tmp < $total_participant_team) {
                    $total_member_individu_must_join = $total_participant_team * 3;
                    throw new BLoCException("jumlah peserta tidak mencukupi, minimal peserta yang harus terdaftar adalah " . $total_member_individu_must_join . ". sedangkan total peserta individu saat ini adalah " . $count_participant_individu . " peserta");
                }
            } else {
                $count_participant_individu_male = ArcheryEventParticipant::where("event_id", $category_team->event_id)
                    ->where("age_category_id", $category_team->age_category_id)
                    ->where("competition_category_id", $category_team->competition_category_id)
                    ->where("distance_id", $category_team->distance_id)
                    ->where("team_category_id", "individu male")
                    ->where("city_id", $city_id)
                    ->get()
                    ->count();

                $count_participant_individu_female = ArcheryEventParticipant::where("event_id", $category_team->event_id)
                    ->where("age_category_id", $category_team->age_category_id)
                    ->where("competition_category_id", $category_team->competition_category_id)
                    ->where("distance_id", $category_team->distance_id)
                    ->where("team_category_id", "individu female")
                    ->where("city_id", $city_id)
                    ->get()
                    ->count();
                if ($count_participant_individu_male == 0 || $count_participant_individu_female == 0) {
                    throw new BLoCException("participant not enought");
                }

                if ($count_participant_individu_male < $total_participant_team) {
                    throw new BLoCException("jumlah peserta tidak mencukupi, minimal peserta male yang harus terdaftar adalah " . $total_participant_team . ". sedangkan total peserta individu male saat ini adalah " . $count_participant_individu_male . " peserta");
                }

                if ($count_participant_individu_female < $total_participant_team) {
                    throw new BLoCException("jumlah peserta tidak mencukupi, minimal peserta female yang harus terdaftar adalah " . $total_participant_team . ". sedangkan total peserta individu female saat ini adalah " . $count_participant_individu_female . " peserta");
                }
            }
            // end blok validasi total peserta


            $penanggung_jawab = User::where("email", $email_pendaftar)->first();
            if (!$penanggung_jawab) {
                throw new BLoCException("user penanggung jawab belum terdaftar");
            }

            $event = ArcheryEvent::find($category_team->event_id);
            if (!$event) {
                throw new BLoCException("event tidak ditemukan");
            }


            $city = City::find($city_id);
            if (!$city) {
                throw new BLoCException("Kota not found");
            }
            if ($city->province_id != $event->province_id) {
                throw new BLoCException("invalid city");
            }

            // insert data participant
            for ($i = 1; $i <= $total_team; $i++) {
                $participant = new ArcheryEventParticipant();
                $participant->club_id = 0;
                $participant->user_id = $penanggung_jawab->id;
                $participant->status = 1;
                $participant->event_id = $event->id;
                $participant->name = $penanggung_jawab->name;
                $participant->type = $penanggung_jawab->category_team;
                $participant->email = $penanggung_jawab->email;
                $participant->phone_number = $penanggung_jawab->phone_number;
                $participant->age = $penanggung_jawab->age;
                $participant->gender = $penanggung_jawab->gender;
                $participant->team_category_id = $category_team->team_category_id;
                $participant->age_category_id = $category_team->age_category_id;
                $participant->competition_category_id = $category_team->competition_category_id;
                $participant->distance_id = $category_team->distance_id;
                $participant->transaction_log_id = 0;
                $participant->unique_id = Str::uuid();
                $participant->event_category_id = $category_team->id;
                $participant->register_by = 2;
                $participant->city_id = $city_id;
                $participant->save();
            }
        }

        if (count($list_errors) > 0) {
            throw new BLoCException("failed", $list_errors);
        }
    }
}
