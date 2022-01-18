<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventMoreInformation;
use App\Models\City;

class ArcheryEvent extends Model
{
    protected $appends = ['event_url', 'flat_categories'];

    public function archeryEventCategories()
    {
        return $this->hasMany(ArcheryEventCategory::class, 'event_id', 'id');
    }

    public function archeryEventQualifications()
    {
        return $this->hasMany(ArcheryEventQualification::class, 'event_id', 'id');
    }

    public function archeryEventRegistrationFees()
    {
        return $this->hasMany(ArcheryEventRegistrationFee::class, 'event_id', 'id');
    }

    public function archeryEventTargets()
    {
        return $this->hasMany(ArcheryEventTarget::class, 'event_id', 'id');
    }

    public function getQualificationSessionLengthAttribute($value)
    {
        return json_decode($value);
    }

    public function getIsFlatRegistrationFeeAttribute($value)
    {
        return $value == 1 || $value == '1';
    }

    public function getQualificationWeekdaysOnlyAttribute($value)
    {
        return $value == 1 || $value == '1';
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    protected function getCategories($id,$type="")
    {
        $categories = ArcheryEventCategoryDetail::select(
                            "archery_event_category_details.id",
                            "archery_event_category_details.quota",
                            "archery_event_category_details.fee",
                            "archery_event_category_details.id AS key",
                            "archery_master_age_categories.label as label_age",
                            "archery_master_age_categories.id as id_age",
                            "archery_master_competition_categories.label as label_competition_categories",
                            "archery_master_competition_categories.id as id_competition_categories",
                            "archery_master_distances.label as label_distances",
                            "archery_master_distances.id as id_distances",
                            "archery_master_team_categories.label as label_team_categories",
                            "archery_master_team_categories.id as id_team_categories",
                            "archery_master_team_categories.type as type",
                            DB::raw("CONCAT(archery_master_team_categories.label,'-',
                                            archery_master_age_categories.label,'-',
                                            archery_master_competition_categories.label,'-',
                                            archery_master_distances.label) AS label"))
                    ->join("archery_master_age_categories","archery_event_category_details.age_category_id","archery_master_age_categories.id")
                    ->join("archery_master_competition_categories","archery_event_category_details.competition_category_id","archery_master_competition_categories.id")
                    ->join("archery_master_distances","archery_event_category_details.distance_id","archery_master_distances.id")
                    ->join("archery_master_team_categories","archery_event_category_details.team_category_id","archery_master_team_categories.id")
                    ->where("archery_event_category_details.event_id",$id)
                    ->orderBy("archery_event_category_details.created_at", "ASC")
                    ->where(function ($query) use ($type){
                        if(!empty($type)){
                            $query->where("archery_master_team_categories.type",$type);
                        }
                     })
                    ->get();


        return $categories;
    }

    public function getEventUrlAttribute()
    {
        return env('WEB_DOMAIN', 'https://my-archery.id') . '/event/' . Str::slug($this->admin->name) . '/' . $this->event_slug;
    }

    public function getFlatCategoriesAttribute()
    {
        $query = "
            SELECT A.id as archery_event_id,
                B.age_category_id, B.for_age, B1.label as age_category_label,
                C.competition_category_id, C1.label as competition_category_label,
                D.team_category_id, D1.label as team_category_label,
                E.distance_id, E1.label as distance_label,
                CONCAT(D1.label, ' - ', B1.label, ' - ', C1.label, ' - ', E1.label) as archery_event_category_label
            FROM archery_events A
            JOIN archery_event_categories B ON A.id = B.event_id
            JOIN archery_event_category_competitions C ON B.id = C.event_category_id
            JOIN archery_event_category_competition_teams D ON C.id = D.event_category_competition_id
            JOIN archery_event_category_competition_distances E ON C.id = E.event_category_competition_id
            JOIN archery_master_age_categories B1 ON B.age_category_id = B1.id
            JOIN archery_master_competition_categories C1 ON C.competition_category_id = C1.id
            JOIN archery_master_team_categories D1 ON D.team_category_id = D1.id
            JOIN archery_master_distances E1 ON E.distance_id = E1.id
            WHERE A.id = :event_id
            ORDER BY D1.label, B1.label, C1.label, E1.label
        ";

        $results = DB::select($query, ['event_id' => $this->id]);

        return $results;
    }
    public static function isOwnEvent($admin_id,$event_id)
    {
      $archery_event =DB::table('archery_events')->where('admin_id', $admin_id)->where('id', $event_id)->first();
      if(!$archery_event){
        return false;
      }else{
        return true;
      }
    }

    protected function detailEventById($id="")
    {
        $datas = ArcheryEvent::select('*','archery_events.id as id_event','cities.id as cities_id','cities.name as cities_name','provinces.id as province_id','provinces.name as provinces_name','admins.name as admin_name',
                'admins.email as admin_email','admin_id')
        ->leftJoin("cities","cities.id","=","archery_events.city_id")
        ->leftJoin("provinces","provinces.id","=","cities.province_id")
        ->leftJoin("admins","admins.id","=","archery_events.admin_id")
        ->where(function ($query) use ($id){
            if(!empty($id)){
                $query->where('archery_events.id',$id);
            }
         })
        ->get();

        

        $output = [];
        foreach ($datas as $key => $data) {
            $admins = Admin::where('id', $data->admin_id)->get();
            
            $more_informations = ArcheryEventMoreInformation::where('event_id', $data->id_event)->get();
            $moreinformations_data=[];
                if ($more_informations) {
                    foreach ($more_informations as $key => $value) {
                        $moreinformations_data[] = [
                            'id' => $value->id,
                            'event_id' => $value->event_id,
                            'title' => $value->title,
                            'description' => $value->description,
                        ];
                    }
                }

            $event_categories = $this->getCategories($data->id_event);
            //dd($event_categories);
            $eventcategories_data=[];
            if ($event_categories) {
                foreach ($event_categories as $key => $value) {
                    $eventcategories_data[] = [
                        'category_details_id' => $value->key,
                        'age_category_id' => ['id' => $value->id_age,
                                            'label' => $value->label_age],
                        'competition_category_id' => ['id' => $value->id_competition_categories,
                                            'label' => $value->label_competition_categories],
                        'distance_id' => ['id' => $value->id_distances,
                                            'label' => $value->label_distances],
                        'team_category_id' => ['id' => $value->id_team_categories,
                                            'label' => $value->label_team_categories],
                        'quota' => $value->quota,
                        'fee' => $value->fee,
                    ];
    
                }
            }
           
            $output[] = array(
                             "id"=> $data->id_event,
                            "event_type"=> $data->event_type,
                            "event_competition"=> $data->event_competition,
                            "public_information"=>['event_name' => $data->event_name,
                            'event_banner' => $data->poster,
                            'event_description' => $data->description,
                            'event_location' => $data->location,
                            'event_city' => ['city_id' => $data->cities_id,
                                            'name_city' => $data->cities_name,
                                            'province_id' => $data->province_id,
                                            'province_name' => $data->provinces_name
                                            ],
                            'event_location_type' => $data->location_type,
                            'event_start_register' => $data->registration_start_datetime,
                            'event_end_register' => $data->registration_end_datetime,
                            'event_start' => $data->event_start_datetime,
                            'event_end' => $data->event_end_datetime,
                            'event_status' => $data->status],
                            'more_information' => $moreinformations_data,
                            'event_categories' => $eventcategories_data,
                            'admins' => $admins,
                            
                        );
            
            unset($moreinformations_data);   
            unset($eventcategories_data);     
        }
         
        return $output;
    }
}
