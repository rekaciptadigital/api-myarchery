<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetArcheryCategoryDetail extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id=$parameters->get('event_id');
        if(!$event_id){
            $archery_category_detail = ArcheryEvent::getCategories('null');
        }else{
            $archery_category_detail = ArcheryEvent::getCategories($event_id);
        }
        
        $type=$parameters->get('type');
        $output= [];
       

        foreach ($archery_category_detail as $key => $value ){
            
            if(!$type){
                $output[$value['label_competition_categories']][]= (object) [
                    'event_category_details_id' => $value['key'],
                    'age_category' => $value['label_age'],
                    'competition_category' => $value['label_competition_categories'],
                    'distances_category' => $value['label_distances'],
                    'team_category' => $value['label_team_categories'],
                    'type' => $value['type'],
                ];
            }elseif(strtolower($type) == 'individual') {
                if($value['type'] == 'Individual'){
                    $output[$value['label_competition_categories']][]= (object) [
                        'event_category_details_id' => $value['key'],
                        'age_category' => $value['label_age'],
                        'competition_category' => $value['label_competition_categories'],
                        'distances_category' => $value['label_distances'],
                        'team_category' => $value['label_team_categories'],
                        'type' => $value['type'],
                    ];
                }
            }elseif(strtolower($type) == 'team') {
                if($value['type'] == 'Team'){
                    $output[$value['label_competition_categories']][]= (object) [
                        'event_category_details_id' => $value['key'],
                        'age_category' => $value['label_age'],
                        'competition_category' => $value['label_competition_categories'],
                        'distances_category' => $value['label_distances'],
                        'team_category' => $value['label_team_categories'],
                        'type' => $value['type'],
                    ];
                }
            
            }
           
        }
        
        return $output;
    }

}