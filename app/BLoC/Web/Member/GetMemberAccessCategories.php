<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
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
        
        $participant = ArcheryEventParticipant::where("archery_event_participants.id", $participant_id)
        ->leftJoin("users","users.id","archery_event_participants.user_id")->first();

        if(!$participant){
            throw new BLoCException("participant not found");
        }
        //dd($participant->event_id);
        $age = floor((time() - strtotime($participant->date_of_birth)) / 31556926);
        if($age == 52){
            
        }
        
        $categories = ArcheryEventCategoryDetail::select('archery_event_category_details.*')
        ->leftJoin("archery_master_age_categories","archery_master_age_categories.id","archery_event_category_details.age_category_id")
        ->where("archery_event_category_details.event_id", $participant->event_id)
        ->whereRaw("substring(archery_event_category_details.team_category_id,1,8) = 'individu' ")
        ->get();
        
        
        $list_category=null;

        foreach($categories as $category){
            if($age ==52){
                $list_category[]=$category;
            }
            else if($category->max_age ==0 && $category->min_age ==0  )
                $list_category[]=$category;
            else if($category->max_age ==0){
                if($category->min_age <=$age){
                    $list_category[]=$category;
                }
            }else if($category->max_age !=0){
                if($category->max_age >=$age){
                    $list_category[]=$category;
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
