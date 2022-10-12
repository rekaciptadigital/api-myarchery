<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Libraries\Upload;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ParticipantMemberTeam;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Mockery\Generator\Parameter;

class Refund extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $participant_id = $parameters->get("participant_id");

        $participant = ArcheryEventParticipant::find($participant_id);
        if (!$participant) {
            throw new BLoCException("participant tidak tersedia");
        }

        $user = User::find($participant->user_id);
        if (!$user) {
            throw new BLoCException("user tidak ada");
        }

        $event = ArcheryEvent::find($participant->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        // if ($event->admin_id != $admin->id) {
        //     throw new BLoCException("forbiden");
        // }

        if ($participant->status != 1) {
            throw new BLoCException("tidak bisa melakukan refund");
        }

        $category_participant = ArcheryEventCategoryDetail::find($participant->event_category_id);
        if (!$category_participant) {
            throw new BLoCException("kategori tidak ditemukan");
        }

        $now = Carbon::now();
        $new_format = Carbon::parse($category_participant->start_event);
        if ($now > $new_format) {
            throw new BLoCException("event telah lewat");
        }
        if ($new_format->diffInDays($now) < 1) {
            // throw new BLoCException("tidak dapat refund, minimal refund adalah 24 jam sebelum berlangsungnya event");
        }

        $image_refund = "";
        if ($parameters->get("image_refund")) {
            if ($participant->upload_image_refund == $parameters->get("image_refund")) {
                $image_refund = $participant->upload_image_refund;
            } else {
                $array_file_index_0 = explode(";", $parameters->get("image_refund"))[0];
                $ext_file_upload =  explode("/", $array_file_index_0)[1];
                if ($ext_file_upload != "jpg" && $ext_file_upload != "jpeg" && $ext_file_upload != "png") {
                    throw new BLoCException("mohon inputkan tipe data gambar png, jpeg, jpg");
                }
                $image_refund = Upload::setPath("asset/image_refund/")->setFileName("image_refund_" . $participant->id)->setBase64($parameters->get('image_refund'))->save();
            }
        }

        $participant->update([
            "status" => 5,
            "upload_image_refund" => $image_refund,
            "reason_refund" => $parameters->get("reason_refund")
        ]);

        $transaction_log = TransactionLog::find($participant->transaction_log_id);
        if (!$transaction_log) {
            throw new BLoCException("data transaksi tidak ditemukan");
        }

        $transaction_log->update([
            "status" => 5
        ]);

        if ($category_participant->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE) {
            return $this->refundIndividu($participant_id, $category_participant->id, $user->id);
        } else {
            return $this->refundTeam($participant->id);
        }
    }

    protected function validation($parameters)
    {
        return [
            "participant_id" => "required|integer",
            "reason_refund" => "required"
        ];
    }

    private function refundTeam($participant_id)
    {
        $participant = ArcheryEventParticipant::find($participant_id);
        if (!$participant) {
            throw new BLoCException("participant tidak tersedia");
        }

        $participant_member_team =  ParticipantMemberTeam::where("participant_id", $participant_id)->get();
        if ($participant_member_team->count() > 0) {
            foreach ($participant_member_team as $pmt) {
                $pmt->delete();
            }
        }

        return $participant;
    }

    private function refundIndividu($participant_id, $category_id, $user_id)
    {
        $participant = ArcheryEventParticipant::find($participant_id);
        if (!$participant) {
            throw new BLoCException("participant tidak tersedia");
        }

        $category_participant = ArcheryEventCategoryDetail::find($category_id);
        if (!$category_participant) {
            throw new BLoCException("kategori tidak ditemukan");
        }

        $user = User::find($user_id);
        if (!$user) {
            throw new BLoCException("user tidak tersedia");
        }

        $participant_memmber = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
        if (!$participant_memmber) {
            throw new BLoCException("data participant member tidak tersedia");
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
        if ($member_number_prefix) {
            $member_number_prefix->delete();
            // throw new BLoCException("member number tidak ditemukan");
        }

        $participant_number = ArcheryEventParticipantNumber::where("participant_id", $participant->id)->first();
        if ($participant_number) {
            $participant_number->delete();
            // throw new BLoCException("participant number tidak ditemukan");
        }

        return $participant;
    }
}
