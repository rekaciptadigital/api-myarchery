<?php 

namespace App\Libraries;

use Illuminate\Support\Facades\Storage;
use Validator;

class Asset{
    
    protected $dirAsset = "/assets";

    protected $dirFile = array();

    public function __construct()
    {
    }

    public static function uploadImage($filePathName,$image, $base64=false)
    {
        $filePathName = "/assets/".$filePathName;
        if(!empty($image)){
            if($base64){
                $content = base64_decode($image);
                if (preg_match('/^data:image\/(\w+);base64,/', $image)) {
                    $data = substr($image, strpos($image, ',') + 1);

                    $content = base64_decode($data);
                }
            }else
                $content = file_get_contents($image);
            $image = Storage::disk(env('STOREG_DISK'))->put($filePathName,$content,'public');
            if($image){
                $url = Storage::disk(env('STOREG_DISK'))->url($filePathName)."#".time();
                print_r($url);exit;
                return $url;
            }
                return "";
        }else{
            return "";
        }
    }

    public static function uploadFile($filePathName,$image, $base64=false)
    {
        if(!empty($image)){
            if($base64)
                $content = base64_decode($image);
            else
                $content = file_get_contents($image);
            $image = Storage::disk(env('STOREG_DISK'))->put($filePathName,$content,'public');
            if($image){
                $url = substr(Storage::disk(env('STOREG_DISK'))->url($filePathName), 1);
                return $url;
            }
                return "";
        }else{
            return "";
        }
    }

    public static function uploadChatFile($filePathName,$image, $base64=false)
    {
        if(!empty($image)){
            if($base64)
                $content = base64_decode($image);
            else
                $content = file_get_contents($image);
            $image = Storage::disk(env('STOREG_DISK'))->put($filePathName,$content,'public');
            if($image){
                $url = Storage::disk(env('STOREG_DISK'))->url($filePathName)."#".time();
                if(env('STOREG_DISK') == "public"){
                    $url = env('STOREG_PUBLIC_DOMAIN').$url;
                }
                return $url;
            }
                return "";
        }else{
            return "";
        }
    }

    public static function deleteImage($url)
    {
        if(!empty($url)){
            $path = env("GOOGLE_CLOUD_STORAGE_API_URI");
            $image = Storage::disk(env('STOREG_DISK'))->delete(\str_replace($path,"",$url));
            if($image){
                return true;
            }
                return false;
        }else{
            return false;
        }
    }
    
}