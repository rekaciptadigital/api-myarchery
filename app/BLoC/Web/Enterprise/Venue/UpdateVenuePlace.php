<?php

namespace App\BLoC\Web\Enterprise\Venue;

use App\Models\VenuePlace;
use App\Models\VenuePlaceFacility;
use App\Models\VenuePlaceGallery;
use App\Models\VenueMasterPlaceFacility;
use App\Models\Provinces;
use App\Models\City;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;
use App\Libraries\Upload;
use App\Jobs\VenuePlaceSubmissionEmailJob;
use Queue;

class UpdateVenuePlace extends Transactional
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
            'eo_id' => $admin->eo_id,
            'name' => $parameters->get('name'),
            'place_type' => $parameters->get('type'),
            'status' => $parameters->get('status'),
            'description' => $parameters->get('description'),
            'phone_number' => $parameters->get('phone_number'),
            'address' => $parameters->get('address'),
            'latitude' => $parameters->get('latitude'),
            'longitude' => $parameters->get('longitude'),
            'province_id' => $parameters->get('province_id'),
            'city_id' => $parameters->get('city_id'),
        ]);

    
        // -------------------------------- add place's facilities -------------------------------- //

            // delete all current facilities
            $current_venue_facilities = VenuePlaceFacility::where('place_id', $venue_place->id)->get();
            foreach ($current_venue_facilities as $venue_facilities) {
                $venue_facilities->delete();
            }

            // add new facilities
            $main_facilities = $parameters->get('facilities', []); 
            $current_other_facilities = $parameters->get('current_other_facilities', []); 
            $facilities = array_merge($main_facilities, $current_other_facilities);
            if (count($facilities) > 0) {
                foreach ($facilities as $key => $value) {
                    $venue_facilities = new VenuePlaceFacility();
                    $venue_facilities->place_id = $venue_place->id;
                    $venue_facilities->master_place_facility_id = $value;
                    $venue_facilities->save();
                }
            }

        // -------------------------------- end add place's facilities -------------------------------- //


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
                
                $gallery_result = Upload::setPath("asset/venue_place/")->setFileName("venue_place_" . $parameters->get("name") . "_" .date("YmdHis"))->setBase64($value)->save();

                $gallery = new VenuePlaceGallery();
                $gallery->place_id = $venue_place->id;
                $gallery->file = $gallery_result;
                $gallery->save();
            }
        }

        $data = VenuePlace::detailVenueById($venue_place->id);

        // send email submission
        if ($venue_place->status == 2) {
            $this->sendMail($data, $admin);
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer",
            "name" => "required",
            "address" => "required",
            "latitude" => "required",
            "longitude" => "required",
            "province_id" => "required",
            "status" => "required|integer",
            "city_id" => "required",
            "type" => "required|in:Indoor,Outdoor,Both",
            "facilities" => "array",
            "other_facilities" => "array",
            "galleries" => "array"
        ];
    }

    private function sendMail($data, $admin) 
    {
        $data = [
            'email' => 'fitrianggraini96@gmail.com',
            'vm_name' => $admin->name,
            'place_name' => $data->name,
            'description' => $data->description,
            'type' => $data->place_type,
            'phone_number' => $data->phone_number,
            'address' => $data->address,
            'province' => Provinces::select('name')->where('id', $data->province_id)->first(),
            'city' => City::select('name')->where('id', $data->city_id)->first(),
            'facilities' => $data->facilities,
            'galleries' => $data->galleries,
        ];
        return Queue::push(new VenuePlaceSubmissionEmailJob($data));
    }

}