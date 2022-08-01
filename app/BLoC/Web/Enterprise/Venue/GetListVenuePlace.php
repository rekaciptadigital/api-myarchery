<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlace;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetListVenuePlace extends Retrieval
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

        $venue_places = VenuePlace::listVenueByEoId($limit, $offset, $admin->eo_id, $filter_status);

        return $venue_places;
    }

    protected function validation($parameters)
    {
        return [
            'limit' => 'required|integer',
            'page' => 'required|integer',
            'status' => 'integer'
        ];
    }
}
