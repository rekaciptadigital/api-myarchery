<?php

namespace App\BLoC\App\Series;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySeriesUserPoint;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class SetMemberSeries extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $member_id = $parameters->get("member_id");
        $category_id = $parameters->get("category_id");
        $user_login =  $user = Auth::guard('app-api')->user();
        $member = ArcheryEventParticipantMember::find($member_id);
        if (!$member) {
            throw new BLoCException("member not found");
        }

        if ($member->user_id != $user_login->id) {
            throw new BLoCException("forbiden");
        }

        if ($user->verify_status != 1) {
            throw new BLoCException("akun anda belum terverifikasi");
        }

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("kategori tidak tersedia");
        }

        $detail_category = $category->getCategoryDetailById($category->id);
        if ($detail_category["have_series"] != 1) {
            throw new BLoCException("kategori di event ini tidak termasuk series");
        }


        if ($member->is_series == 0) {
            $check_is_exist_join_series_by_user = ArcheryEventParticipantMember::select("archery_event_participant_members.*")->join("archery_event_participants", "archery_event_participants.id", '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where("archery_event_participant_members.user_id", $user_login->id)
                ->where("archery_event_participant_members.is_series", 1)
                ->where("archery_event_participants.event_id", $category->event_id)
                ->get();

            if ($check_is_exist_join_series_by_user->count() > 0) {
                throw new BLoCException("anda telah mendaftar series di kategori lain pada event lain");
            }

            $member->update([
                "is_series" => 1
            ]);

            ArcherySeriesUserPoint::where("member_id", $member->id)->update([
                "status" => 1
            ]);
        } else {
            $member->update([
                "is_series" => 0
            ]);

            ArcherySeriesUserPoint::where("member_id", $member->id)->update([
                "status" => 0
            ]);
        }

        return $member;
    }

    protected function validation($parameters)
    {
        return [
            "member_id" => "required|integer",
            "category_id" => "required|integer"
        ];
    }
}
