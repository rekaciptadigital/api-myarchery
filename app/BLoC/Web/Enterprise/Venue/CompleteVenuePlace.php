<?php

namespace App\BLoC\Web\Enterprise\Venue;

use App\Models\VenuePlace;
use App\Models\VenuePlaceCapacityArea;
use App\Models\VenueMasterPlaceCapacityArea;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class CompleteVenuePlace extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $venue_place = VenuePlace::find($parameters->get('id'));
        if (!$venue_place) throw new BLoCException("Data not found");
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        // venue place
        $venue_place->update([
            'budrest_quantity' => $parameters->get('budrest_quantity'),
            'target_quantity' => $parameters->get('target_quantity'),
            'arrow_quantity' => $parameters->get('arrow_quantity'),
            'people_quantity' => $parameters->get('people_quantity'),
            'status' => 4, //aktif
        ]);

        // place's capacity area
        $main_capacity_area = $parameters->get('capacity_area', []); 
        $current_capacity_area = $parameters->get('current_capacity_area', []); 
        $capacity_area = array_merge($main_capacity_area, $current_capacity_area);
        if (count($capacity_area) > 0) {
            foreach ($capacity_area as $key => $value) {
                $venue_capacity_area = new VenuePlaceCapacityArea();
                $venue_capacity_area->place_id = $venue_place->id;
                $venue_capacity_area->master_place_capacity_area_id = $value;
                $venue_capacity_area->save();
            }
        }

        // place's other capacity area
        $other_capacity_area = $parameters->get('other_capacity_area', []); 
        if (count($other_capacity_area) > 0) {
            foreach ($other_capacity_area as $key => $value) {
                $venue_master_capacity_area = new VenueMasterPlaceCapacityArea();
                $venue_master_capacity_area->distance = $value;
                $venue_master_capacity_area->eo_id = $admin->eo_id;
                $venue_master_capacity_area->save();

                $venue_capacity_area_2 = new VenuePlaceCapacityArea();
                $venue_capacity_area_2->place_id = $venue_place->id;
                $venue_capacity_area_2->master_place_capacity_area_id = $venue_master_capacity_area->id;
                $venue_capacity_area_2->save();
            }
        }

        $data = VenuePlace::detailVenueById($venue_place->id);

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer"
        ];
    }

}