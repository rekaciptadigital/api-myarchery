<?php

namespace App\BLoC\General;

use App\Exports\MemberContingentTeamExport;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\City;
use App\Models\ExcellCollective;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportMemberCollectiveTeam extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $event_id = $parameters->get("event_id");
        $city_id = $parameters->get("city_id");
        $list_teams = $parameters->get("list_teams");

        $event = ArcheryEvent::find($event_id);
        if ($event->with_contingent != 1) {
            throw new BLoCException("event must be with_contingent_format");
        }

        $province_id = $event->province_id;

        $city = City::find($city_id);
        if ($city->province_id != $province_id) {
            throw new BLoCException("province and city invalid");
        }

        $new_list_team = [];
        $total_price = 0;
        foreach ($list_teams as $team) {
            $category_id = $team["category_id"];
            $count_team = $team["count_team"];

            $category_team = ArcheryEventCategoryDetail::where("id", $category_id)
                ->where("event_id", $event_id)
                ->first();

            if (!$category_team) {
                throw new BLoCException("category not found");
            }

            if (strtolower($category_team->category_team) == "individual") {
                throw new BLoCException("category must be team type");
            }

            $count_participant_team = ArcheryEventParticipant::where("event_id", $event_id)
                ->where("age_category_id", $category_team->age_category_id)
                ->where("competition_category_id", $category_team->competition_category_id)
                ->where("distance_id", $category_team->distance_id)
                ->where("team_category_id", $category_team->team_category_id)
                ->where("city_id", $city_id)
                ->get()
                ->count();

            $total_participant_team = $count_team + $count_participant_team;


            // validasi total peserta individu untuk pendaftaran beregu
            if ($category_team->team_category_id == "male_team" || $category_team->team_category_id == "female_team") {
                $team_category_id = $category_team->team_category_id == "male_team" ? "individu male" : "individu female";
                $count_participant_individu = ArcheryEventParticipant::where("event_id", $event_id)
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
                $count_participant_individu_male = ArcheryEventParticipant::where("event_id", $event_id)
                    ->where("age_category_id", $category_team->age_category_id)
                    ->where("competition_category_id", $category_team->competition_category_id)
                    ->where("distance_id", $category_team->distance_id)
                    ->where("team_category_id", "individu male")
                    ->where("city_id", $city_id)
                    ->get()
                    ->count();

                $count_participant_individu_female = ArcheryEventParticipant::where("event_id", $event_id)
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

            $city = City::find($city_id);
            $total_price += (int)$category_team->fee * $count_team;


            $team["city_id"] = $city_id;
            $team["city_name"] = $city->name;
            $team["email_participant"] = $user->email;
            $team["responsible_name"] = $user->name;
            $team["category_label"] = $category_team->label_category;

            $new_list_team[] = $team;
        }

        $file_name = "member_collective_team_" . $user->id . "_" . $city_id . "_" . time() . "_.xlsx";
        $final_doc = '/member_collective/' . $event_id . '/' . $file_name;
        $excel = new MemberContingentTeamExport($new_list_team);
        Excel::store($excel, $final_doc, 'public');
        $destinationPath = Storage::url($final_doc);
        $file_path = env('STOREG_PUBLIC_DOMAIN') . $destinationPath;

        ExcellCollective::saveExcellCollective($user->id, $event_id, $city_id, $file_path);

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
        $rules["list_teams"] = "required|array";
        $rules["list_teams.*.category_id"] = "required|exists:archery_event_category_details,id";
        $rules["list_teams.*.count_team"] = "required|numeric|min:1";

        return $rules;
    }
}
