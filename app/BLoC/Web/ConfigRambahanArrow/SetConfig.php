<?php

namespace App\BLoC\Web\ConfigRambahanArrow;

use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\CategoryConfigMappingArrowRambahan;
use App\Models\ConfigArrowRambahan;
use App\Models\GroupCategory;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class SetConfig extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user(); // admin admin login

        // tangkap semua params
        $event_id = $parameters->get('event_id');
        $is_active_config = $parameters->get("is_active_config");
        $st_to_all_category = $parameters->get("st_to_all_category");
        $rules_shooting = $parameters->get("rules_shooting");


        $event = ArcheryEvent::find($event_id);

        // cek pemilik event
        if ($event->admin_id != $admin->id) {
            throw new BLoCException("you are not owner this event");
        }

        // reset config
        $list_category = ArcheryEventCategoryDetail::where("event_id", $event_id)->get(); // tangkap semua kategori

        // ubah jumlah arrow dan rambahan di semua kategori menjadi nilai default
        foreach ($list_category as $category) {
            $category->count_stage = 6;
            $category->count_shot_in_stage = 6;
            $category->save();
        }

        // hapus semua kategori config
        $config = ConfigArrowRambahan::where("event_id", $event_id)->first();

        if ($config) {
            if ($config->type == 2) { // jika config type untuk kategori khusus
                $list_config_category = CategoryConfigMappingArrowRambahan::where("config_arrow_rambahan_id", $config->id)->get(); // tangkap semua list_category khusus
                // delete semua config categori khusus
                foreach ($list_config_category as $category_config) {
                    $category_config->delete();
                }
            }
        }
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer|exists:archery_events,id",
        ];
    }
}
