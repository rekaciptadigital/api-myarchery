<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlaceGallery;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\File;

class DeleteImageVenuePlace extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $gallery = VenuePlaceGallery::find($parameters->get('id'));
        if (!$gallery) throw new BLoCException("Data not found");

        try {
            $explode = explode(env('APP_HOSTNAME'), $gallery->file); 

            if (count($explode) === 2) {
                $image_path = $explode[1];
            } else {
                $image_path = $explode[0];
            }

            $explode_file_path = explode('#', $image_path);
            
            if(File::exists($explode_file_path[0])) {
                File::delete($explode_file_path[0]);
            }

            $gallery->delete();

            return true;

        } catch (\Exception $e) {            
            return $e->getMessage();
        }
        
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer',
        ];
    }
}
