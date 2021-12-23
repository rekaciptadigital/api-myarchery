<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use App\Models\ClubMember;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateArcheryClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $archery_club = new ArcheryClub();
        $archery_club->name = $parameters->get('name');
        $archery_club->place_name = $parameters->get('place_name');
        $archery_club->province = $parameters->get('province');
        $archery_club->city = $parameters->get('city');
        $archery_club->address = $parameters->get('address');
        $archery_club->description = $parameters->get('description');
        if ($parameters->get('logo')) {
            $folderPath = "logo/";

            $image_parts = explode(";base64,", $parameters->get('logo'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = $folderPath . time() . '.'.$image_type;
    
            file_put_contents($file, $image_base64);
            $archery_club->logo = $file;
        }
        $archery_club->save();

        $club_member = new ClubMember();
        $club_member->user_id = $user->id;
        $club_member->club_id = $archery_club->id;
        $club_member->status = 1;
        $club_member->role = 1;
        $club_member->save();


        return $archery_club;
    }

    protected function validation($parameters)
    {
        return [
            'name' => 'required|string|unique:archery_clubs',
            'place_name' => 'required|string',
            'province' => "required|string",
            'city' => 'required|string',
            'logo' => 'required|string',
            'address' => 'required|string',
            'description' => 'string'
        ];
    }
}