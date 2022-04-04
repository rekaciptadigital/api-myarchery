<?php

namespace App\BLoC\Web\Image;

use Illuminate\Http\Request;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Storage;



class Image extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $image=$parameters->get("image");
        
        $imageName = time().'.'.$image->extension();  

        $path = Storage::disk('s3')->put('images', $image);

        $path = Storage::disk('s3')->url($path);

        return  $path;
        
    }

    protected function validation($parameters)
    {
        return [
            'image' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
