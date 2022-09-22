<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenuePlace extends Model
{
    protected $guarded = [];

    protected function detailVenueById($id)
    {
        $data = VenuePlace::where('venue_places.id', $id)->first();        

        $city = City::find($data->city_id);
        $data->city = [
            "id" => $city ? $city->id : 0, 
            "name" => $city ? $city->name : ""
        ];

        $province_id = $city ? $city->province_id : 0;
        $province = Provinces::find($province_id);
        $data->province = [
                        "id" => $province ? $province->id : 0 , 
                        "name" => $province ? $province->name : ""
        ];

        $data['facilities'] = VenuePlaceFacility::select('venue_place_facilities.master_place_facility_id as id', 'venue_master_place_facilities.name as name')
                                ->leftJoin("venue_master_place_facilities", "venue_master_place_facilities.id", "=", "venue_place_facilities.master_place_facility_id")
                                ->where("venue_place_facilities.place_id", "=", $id)
                                ->where("venue_master_place_facilities.eo_id", "=", 0)
                                ->get();  
        $data['other_facilities'] = VenuePlaceFacility::select('venue_place_facilities.master_place_facility_id as id', 'venue_master_place_facilities.name as name')
                                ->leftJoin("venue_master_place_facilities", "venue_master_place_facilities.id", "=", "venue_place_facilities.master_place_facility_id")
                                ->where("venue_place_facilities.place_id", "=", $id)
                                ->where("venue_master_place_facilities.eo_id", "!=", 0)
                                ->get();                       
                                 
        $data['galleries'] = VenuePlaceGallery::where("place_id", "=", $id)->get();
                                        
        $data['capacity_area'] = VenuePlaceCapacityArea::select('venue_place_capacity_area.master_place_capacity_area_id as id', 'venue_master_place_capacity_area.distance as distance')
                                ->leftJoin("venue_master_place_capacity_area", "venue_master_place_capacity_area.id", "=", "venue_place_capacity_area.master_place_capacity_area_id")
                                ->where("venue_place_capacity_area.place_id", "=", $id)
                                ->where("venue_master_place_capacity_area.eo_id", "=", 0)
                                ->get();  
        $data['other_capacity_area'] = VenuePlaceCapacityArea::select('venue_place_capacity_area.master_place_capacity_area_id as id', 'venue_master_place_capacity_area.distance as distance')
                                ->leftJoin("venue_master_place_capacity_area", "venue_master_place_capacity_area.id", "=", "venue_place_capacity_area.master_place_capacity_area_id")
                                ->where("venue_place_capacity_area.place_id", "=", $id)
                                ->where("venue_master_place_capacity_area.eo_id", "!=", 0)
                                ->get();  
                       
        return $data;
    }

    protected function listVenueByEoId($limit, $offset, $eo_id, $filter_status = '')
    {
        $datas = VenuePlace::where('eo_id', $eo_id);  

        // filter by status
        $datas->when($filter_status, function ($query) use ($filter_status) {
            return $query->where("status", $filter_status);
        });

        $data_collection = $datas->orderBy('created_at', 'DESC')->limit($limit)->offset($offset)->get();
   
        $output = [];
        foreach ($data_collection as $data) {
            $galleries = VenuePlaceGallery::where("place_id", "=", $data->id)->get();   
            $galleries_data = [];
            if ($galleries) {
                foreach ($galleries as $key => $value) {
                    $galleries_data[] = [
                        'id' => $value->id,
                        'file' => $value->file
                    ];
                }
            }

            $data['galleries'] = $galleries_data;
            array_push($output, $data);
        }
        return $output;
    }

    protected function getAllListVenue($filter_status = '')
    {
        $datas = VenuePlace::query();  

        // filter by status
        $datas->when($filter_status, function ($query) use ($filter_status) {
            return $query->where("status", $filter_status);
        });

        $data_collection = $datas->get();
   
        $result = [];
        foreach ($data_collection as $data) {
            $admin_venue = Admin::where('eo_id', $data->eo_id)->first();
            $facilities = VenuePlaceFacility::select('venue_place_facilities.master_place_facility_id as id', 'venue_master_place_facilities.name as name')
                            ->leftJoin("venue_master_place_facilities", "venue_master_place_facilities.id", "=", "venue_place_facilities.master_place_facility_id")
                            ->where("venue_place_facilities.place_id", "=", $data->id)
                            ->get(); 
            $facilities_data = [];
            if ($facilities) {
                foreach ($facilities as $key => $value) {
                    $facilities_data[] = [
                        'id' => $value->id,
                        'name' => $value->name
                    ];
                }
            }
            
            $galleries = VenuePlaceGallery::where("place_id", "=", $data->id)->get();   
            $galleries_data = [];
            if ($galleries) {
                foreach ($galleries as $key => $value) {
                    $galleries_data[] = [
                        'id' => $value->id,
                        'file' => $value->file
                    ];
                }
            }

            $capacity_area = VenuePlaceCapacityArea::select('venue_place_capacity_area.master_place_capacity_area_id as id', 'venue_master_place_capacity_area.distance as distance')
                            ->leftJoin("venue_master_place_capacity_area", "venue_master_place_capacity_area.id", "=", "venue_place_capacity_area.master_place_capacity_area_id")
                            ->where("venue_place_capacity_area.place_id", "=", $data->id)
                            ->get(); 
            $capacity_area_data = [];
            if ($capacity_area) {
                foreach ($capacity_area as $key => $value) {
                    $capacity_area_data[] = [
                        'id' => $value->id,
                        'distance' => $value->distance
                    ];
                }
            }

            $data['admin'] = $admin_venue;
            $data['facilities'] = $facilities_data;
            $data['galleries'] = $galleries_data;
            $data['capacity_area'] = $capacity_area_data;
            array_push($result, $data);
        }
        return $result;
    }
}
