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
                                ->get();  
        return $data;
    }
}
