<?php

namespace App\Libraries;

class Common
{

    public static function removeDir($dir)
    {
        if (!file_exists($dir)) return true;
        
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
              if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") 
                   self::removeDir($dir."/".$object); 
                else unlink   ($dir."/".$object);
              }
            }
            reset($objects);
            rmdir($dir);
        }
        return true;
    }
}
