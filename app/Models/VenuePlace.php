<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenuePlace extends Model
{
    protected $guarded = [];

    protected function detailVenueById($id)
    {
        $data = VenuePlace::where('venue_places.id', $id)->first();        
        $data['facilities'] = VenuePlaceFacility::select('venue_place_facilities.master_place_facility_id as id', 'venue_master_place_facilities.name as name')
                                ->leftJoin("venue_master_place_facilities", "venue_master_place_facilities.id", "=", "venue_place_facilities.master_place_facility_id")
                                ->where("venue_place_facilities.place_id", "=", $id)
                                ->get();  
        $data['galleries'] = VenuePlaceGallery::where("place_id", "=", $id)->get();                        
        return $data;
    }

    protected function listVenueByEoId($limit, $offset, $eo_id)
    {
        $datas = VenuePlace::where('eo_id', $eo_id)
                ->limit($limit)->offset($offset)
                ->get();  
                
        $output = [];
        foreach ($datas as $data) {
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

            $output[] = array(
                "venue_place" => $data,
                "galleries" => $galleries_data
            );
        }
        return $output;
    }
}
