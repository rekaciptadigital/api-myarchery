<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class DeleteCategoryDetailV2 extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $find = ArcheryEventCategoryDetail::find($parameters->get('category_id'));
        if ($find) {
            $event = ArcheryEvent::find($find->event_id);
            if (!$event) {
                throw new BLoCException("event tidak ditemukan");
            }
            if ($event->admin_id != $admin->id) {
                throw new BLoCException("forbiden");
            }

            $check = ArcheryEventParticipant::where('event_category_id', $find->id)->first();

            if ($check) {
                throw new BLoCException("sudah ada partisipan");
            }

            $find->delete();
        } else {
            throw new BLoCException("category tidak ditemukan");
        }

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "category_id" => "required"
        ];
    }
}
