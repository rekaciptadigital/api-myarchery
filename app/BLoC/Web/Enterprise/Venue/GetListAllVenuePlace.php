<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlace;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetListAllVenuePlace extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;
        $filter_status = $parameters->get("status");
        $filter_type = $parameters->get("place_type");
        $name = $parameters->get("name"); // search by name


        $venue_places = VenuePlace::getAllListVenue($filter_status, $filter_type, $name, $limit, $offset);

        return $venue_places;
    }

    protected function validation($parameters)
    {
        return [
            'status' => 'integer'
        ];
    }
}
