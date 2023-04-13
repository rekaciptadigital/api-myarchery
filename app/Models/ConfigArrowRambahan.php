<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigArrowRambahan extends Model
{
    protected $table = "config_arrow_rambahan";
    protected $fillable = ["event_id", "type", "session", "arrow", "rambahan"];

    public static function resetConfigArrowRambahan(ArcheryEvent $archery_event)
    {
        $list_category = ArcheryEventCategoryDetail::where("event_id", $archery_event->id)->get(); // tangkap semua kategori
        // ubah jumlah arrow dan rambahan di semua kategori menjadi nilai default
        foreach ($list_category as $category) {
            $category->count_stage = 6;
            $category->count_shot_in_stage = 6;
            $category->session_in_qualification = 2;
            $category->save();
        }

        // hapus semua kategori config
        $config = ConfigArrowRambahan::where("event_id", $archery_event->id)->first();

        if ($config) {
            if ($config->type == 2) { // jika config type untuk kategori khusus
                $list_category_config = CategoryConfig::where("config_arrow_rambahan_id", $config->id)
                    ->get(); // tangkap semua config list_category khusus

                // delete semua config categori khusus
                foreach ($list_category_config as $category_config) {
                    $list_category_2 = CategoryConfigMappingArrowRambahan::where("config_category_id", $category_config->id)
                        ->get(); // tangkap semua kategori yang ada
                        
                    // delete semua category
                    foreach ($list_category_2 as $category_2) {
                        $category_2->delete();
                    }
                    $category_config->delete(); // delete category_config
                }
            }

            $config->delete(); // delete config
        }
    }
}
