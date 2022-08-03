<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlace;
use App\Models\VenuePlaceFacility;
use App\Models\VenuePlaceGallery;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\File;

class DeleteDraftVenuePlace extends Retrieval
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

        // 1: draft, 2: diajukan, 3: lengkapi-data, 4: aktif, 5: non-aktif, 6:ditolak
        if ($venue_place->status == 1) {

            // delete all current facilities
            $current_venue_facilities = VenuePlaceFacility::where('place_id', $venue_place->id)->get();
            foreach ($current_venue_facilities as $venue_facilities) {
                $venue_facilities->delete();
            }

            // delete all galleries
            $venue_galleries = VenuePlaceGallery::where('place_id', $venue_place->id)->get();
            foreach ($venue_galleries as $gallery) {
                VenuePlaceGallery::deleteImage($gallery);
            }

            $venue_place->delete();
        } else {
            throw new BLoCException("Gagal menghapus data, status dokumen bukan draft");
        }

        return "success";
        
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer',
        ];
    }
}