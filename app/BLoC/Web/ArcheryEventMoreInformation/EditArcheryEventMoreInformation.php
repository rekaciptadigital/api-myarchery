<?php

namespace App\BLoC\Web\ArcheryEventMoreInformation;

use App\Models\ArcheryEventMoreInformation;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class EditArcheryEventMoreInformation extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        
        $archery_more_information = ArcheryEventMoreInformation::find($parameters->get('id'));
        $archery_more_information->event_id = $parameters->get('event_id');
        $archery_more_information->title = $parameters->get('title');
        $archery_more_information->description = $parameters->get('description');
        $archery_more_information->save();

        return $archery_more_information;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required",
        ];
    }
}
