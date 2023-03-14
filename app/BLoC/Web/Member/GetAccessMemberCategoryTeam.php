<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetAccessMemberCategoryTeam extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant_id = $parameters->get("participant_id");

        // cari participant berdsarkan id dan type nya == Team
        $participant = ArcheryEventParticipant::select("archery_event_participants.*")
            ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_participants.event_category_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_participants.id", $participant_id)
            ->where("archery_master_team_categories.type", "Team")
            ->first();
        if (!$participant) {
            throw new BLoCException("participant not found");
        }

        $event = ArcheryEvent::find($participant->event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        $user = User::find($participant->user_id);

        // pemilihan kategori individu sesuai gender
        if ($user->gender == "male") {
            $list_category_individu_ids = ["individu male", "individu_mix"];
        } else {
            $list_category_individu_ids = ["individu female", "individu_mix"];
        }

        // insert kategori beregu ke list gender agar user individu bisa pindah ke kategori beregu
        $list_category_team_ids = ["male_team", "female_team", "mix_team"];
        $list_team_category_ids = array_merge($list_category_individu_ids, $list_category_team_ids);

        // query category berdasarkan event id dan array team category id
        $categories = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.event_id", $participant->event_id)
            ->whereIn("archery_master_team_categories.id", $list_team_category_ids)
            ->get();

        // cek jika array category kosong
        if ($categories->count() == 0) {
            throw new BLoCException("categories were not found");
        }

        $new_array_categories = [];

        foreach ($categories as $key => $category) {
            $countUserBooking = ArcheryEventParticipant::countEventUserBooking($category->id); // dapatkan total peserta yang booking di setiap category
            $categories[$key]->countUserBooking = $countUserBooking; // tambahkan quota left di response category
            if ($category->type == "Individual") {
                $age_category = ArcheryMasterAgeCategory::find($category->age_category_id);
                if (!$age_category) {
                    throw new BLoCException("age_category not found");
                }
                $check_age = ArcheryEvent::checUserAgeCanOrderCategory($user->date_of_birth, $age_category, $event); // pengecekan apakah usia user sesuai dengan syarat usia category
                if ($check_age == 1) {
                    $new_array_categories[] = $categories[$key]; // jika valid maka masukkan ke array valid category
                }
            } else {
                $new_array_categories[] = $categories[$key]; // insertkan ke array valid category jika category type == Team
            }
        }

        return $new_array_categories;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|exists:archery_event_participants,id',
        ];
    }
}
