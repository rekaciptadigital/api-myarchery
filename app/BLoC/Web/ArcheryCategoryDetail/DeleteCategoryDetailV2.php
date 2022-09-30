<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
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
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $category_ids = $parameters->get("category_ids");
        foreach ($category_ids as $category_id) {
            $find = ArcheryEventCategoryDetail::find($category_id);
            if ($find) {
                if ($find->event_id != $event_id) {
                    throw new BLoCException("event dan category tidak sama");
                }

                $time_now = time();
                $check = ArcheryEventParticipant::select("archery_event_participants.*")->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where('archery_event_participants.event_category_id', $find->id)
                    ->where(function ($query) use ($time_now) {
                        $query->where("archery_event_participants.status", 1)
                            ->orWhere(function ($q) use ($time_now) {
                                $q->where("archery_event_participants.status", 4);
                                $q->where("transaction_logs.status", 4);
                                $q->where("transaction_logs.expired_time", ">", $time_now);
                            });
                    })->get();


                foreach ($check as $c) {
                    if ($c->status == 1) {
                        throw new BLoCException("sudah ada peserta terdaftar");
                    }

                    if ($c->status == 4) {
                        throw new BLoCException("maaf kategori sudah ada yang menunggu pembayaran");
                    }
                }

                $participants = ArcheryEventParticipant::where("event_category_id", $find->id)->get();
                foreach ($participants as $p) {
                    $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $p->id)->first();
                    if ($member) {
                        $member->delete();
                        $p->delete();
                    }
                }

                $find->delete();
            } else {
                throw new BLoCException("category tidak ditemukan");
            }
        }

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
            "category_ids" => "required|array|min:1"
        ];
    }
}
