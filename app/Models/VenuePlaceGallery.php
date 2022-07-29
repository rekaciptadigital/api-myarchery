<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class VenuePlaceGallery extends Model
{
    protected $guarded = [];

    protected function deleteImage($data) {
        $explode = explode(env('APP_HOSTNAME'), $data->file); 

        if (count($explode) === 2) {
            $image_path = $explode[1];
        } else {
            $image_path = $explode[0];
        }

        $explode_file_path = explode('#', $image_path);
        
        if(File::exists($explode_file_path[0])) {
            File::delete($explode_file_path[0]);
        }

        DB::table('venue_place_galleries')->where('id',$data->id)->delete();

        return true;
    }
}
