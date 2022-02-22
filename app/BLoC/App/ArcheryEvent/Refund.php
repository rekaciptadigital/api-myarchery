<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\Admin;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventMoreInformation;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\City;
use App\Models\ParticipantMemberTeam;
use App\Models\Provinces;
use App\Models\TransactionLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class Refund extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $participant_id = $parameters->get("participant_id");

        $participant = ArcheryEventParticipant::find($participant_id);
        if (!$participant) {
            throw new BLoCException("participant tidak tersedia");
        }

        if ($participant->user_id != $user->id) {
            throw new BLoCException("forbiden");
        }

        if ($participant->status != 1) {
            throw new BLoCException("tidak bisa melakukan refund");
        }

        $participant_memmber = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
        if (!$participant_memmber) {
            throw new BLoCException("data participant member tidak tersedia");
        }

        $category_participant = ArcheryEventCategoryDetail::find($participant->event_category_id);
        $now = Carbon::now();
        $new_format = Carbon::parse($category_participant->start_event);
        if ($now > $new_format) {
            throw new BLoCException("event telah lewat");
        }
        if ($new_format->diffInDays($now) < 1) {
            throw new BLoCException("tidak dapat mengubah kategori, minimal mengubah kategori adalah 24 jam sebelum berlangsungnya event");
        }

        $category_detai_team = ArcheryEventCategoryDetail::where('event_id', $category_participant->event_id)
            ->where('age_category_id', $category_participant->age_category_id)
            ->where('competition_category_id', $category_participant->competition_category_id)
            ->where('distance_id', $category_participant->distance_id)
            ->where(function ($query) use ($user) {
                return $query->where('team_category_id', $user->gender . "_team")->orWhere('team_category_id', 'mix_team');
            })->get();

        if ($category_detai_team->count() > 0) {
            foreach ($category_detai_team as $cdt) {
                $participant_member_team = ParticipantMemberTeam::where('event_category_id', $cdt->id)
                    ->where('participant_member_id', $participant_memmber->id)
                    ->first();

                if ($participant_member_team) {
                    throw new BLoCException("tidak dapat refund karena anda telah terdaftar di team");
                }
            }
        }

        $participant->update([
            "status" => 2
        ]);

        $transaction_log = TransactionLog::find($participant->transaction_log_id);
        if (!$transaction_log) {
            throw new BLoCException("data transaksi tidak ditemukan");
        }

        $transaction_log->update([
            "status" => 2
        ]);

        $participant_member_team =  ParticipantMemberTeam::where("participant_id", $participant->id)->first();
        if (!$participant_member_team) {
            throw new BLoCException("data participant member team tidak tersedia");
        }
        $participant_member_team->delete();

        $qualification_full_day = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_memmber->id)->first();
        if (!$qualification_full_day) {
            throw new BLoCException("data qualification full day tidak ditemukan");
        }
        $qualification_full_day->delete();

        $member_number_prefix =  ArcheryEventParticipantMemberNumber::where("user_id", $participant_memmber->user_id)
            ->where("event_id", $participant->event_id)->first();
        if (!$member_number_prefix) {
            throw new BLoCException("member number tidak ditemukan");
        }
        $member_number_prefix->delete();

        return $participant;
    }

    protected function validation($parameters)
    {
        return [
            "participant_id" => "required|integer"
        ];
    }
}
