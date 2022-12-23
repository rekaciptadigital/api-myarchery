<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetMemberAccessCategories extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $participant_id = $parameters->get("participant_id");

        $participant = ArcheryEventParticipant::find($participant_id);

        if (!$participant) {
            throw new BLoCException("participant not found");
        }

        $user = User::find($participant->user_id);

        $age = $user->age;

        // cek gender participant
        $gender_participant = $user->gender;
        if ($gender_participant == "male") {
            $list_gender = ["individu male", "individu_mix"];
        } else {
            $list_gender = ["individu female", "individu_mix"];
        }

        $categories = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_age_categories.max_age", "archery_master_age_categories.min_age")->join("archery_master_age_categories", "archery_master_age_categories.id", "archery_event_category_details.age_category_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.event_id", $participant->event_id)
            ->whereIn("archery_master_team_categories.id", $list_gender)
            ->get();


        if ($categories->isEmpty()) {
            throw new BLoCException("categories were not found");
        }

        $list_category = null;

        foreach ($categories as $category) {
            $countUserBooking = ArcheryEventParticipant::countEventUserBooking($category->id);
            $category->countUserBooking = $countUserBooking;
            if ($age == 52) {
                $list_category[] = $category;
            } else if ($category->max_age == 0 && $category->min_age == 0)
                $list_category[] = $category;
            else if ($category->max_age == 0) {
                if ($category->min_age <= $age) {
                    $list_category[] = $category;
                }
            } else if ($category->max_age != 0) {
                if ($category->max_age >= $age) {
                    $list_category[] = $category;
                }
            }
        }

        return $list_category;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|exists:archery_event_participants,id',
        ];
    }
}
