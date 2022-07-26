<?php

namespace App\BLoC\Web\Enterprise\Venue;

use App\Models\VenuePlace;
use App\Models\VenuePlaceFacility;
use App\Models\VenuePlaceGallery;
use App\Models\VenueMasterPlaceFacility;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;
use App\Libraries\Upload;

class CreateVenuePlace extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        // venue place
        $venue_place = new VenuePlace();
        $venue_place->eo_id = $admin->eo_id;
        $venue_place->name = $parameters->get('name');
        $venue_place->place_type = $parameters->get('type');
        $venue_place->description = $parameters->get('description');
        $venue_place->phone_number = $parameters->get('phone_number');
        $venue_place->address = $parameters->get('address');
        $venue_place->latitude = $parameters->get('latitude');
        $venue_place->longitude = $parameters->get('longitude');
        $venue_place->province_id = $parameters->get('province_id');
        $venue_place->city_id = $parameters->get('city_id');
        $venue_place->save();

    
        // place's facilities
        $facilities = $parameters->get('facilities', []); 
        if (count($facilities) > 0) {
            foreach ($facilities as $key => $value) {
                $venue_facilities = new VenuePlaceFacility();
                $venue_facilities->place_id = $venue_place->id;
                $venue_facilities->master_place_facility_id = $value;
                $venue_facilities->save();
            }
        }


        // place's other facilities
        $other_facilities = $parameters->get('other_facilities', []); 
        if (count($other_facilities) > 0) {
            foreach ($other_facilities as $key => $value) {
                $venue_master_facilities = new VenueMasterPlaceFacility();
                $venue_master_facilities->name = $value;
                $venue_master_facilities->eo_id = $admin->eo_id;
                $venue_master_facilities->save();

                $venue_facilities_2 = new VenuePlaceFacility();
                $venue_facilities_2->place_id = $venue_place->id;
                $venue_facilities_2->master_place_facility_id = $venue_master_facilities->id;
                $venue_facilities_2->save();
            }
        }

        // image galleries
        $galleries = $parameters->get('galleries', []); 
        if (count($galleries) > 0) {
            foreach ($galleries as $key => $value) {
                $array_file_index_0 = explode(";", $value)[0];
                $ext_file_upload =  explode("/", $array_file_index_0)[1];
                if ($ext_file_upload != "png" && $ext_file_upload != "jpg" && $ext_file_upload != "jpeg") {
                    throw new BLoCException("mohon inputkan tipe data gambar");
                }
                $gallery_result = Upload::setPath("asset/venue_place/")->setFileName("venue_place_" . $parameters->get("name") . "_" .$key)->setBase64($value)->save();

                $gallery = new VenuePlaceGallery();
                $gallery->place_id = $venue_place->id;
                $gallery->file = $gallery_result;
                $gallery->save();
            }
        }

        return VenuePlace::detailVenueById($venue_place->id);
    }

    protected function validation($parameters)
    {
        return [
            "name" => "required",
            "address" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "province_id" => "required",
            "city_id" => "required",
            "type" => "required|in:Indoor,Outdoor,Both",
            "facilities" => "array",
            "other_facilities" => "array",
            "galleries" => "array"
        ];
    }
}
