<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ArcheryEventSerie;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySeriesUserPoint;
use App\Models\TransactionLog;
use App\Models\User;
use DateTime;
use Illuminate\Support\Carbon;

class UpdateParticipantCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $participant_id = $parameters->get('participant_id');
        $category_id = $parameters->get('category_id'); // category id tujuan
        $time_now = time();

        // cek category tujuan apakah tersedia atau tidak
        $new_category = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            "archery_events.admin_id",
            "archery_master_team_categories.type"
        )
            ->join("archery_events", "archery_events.id", "=", "archery_event_category_details.event_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();
        if (!$new_category) {
            throw new BLoCException("Kategori tidak ditemukan");
        }

        $event = ArcheryEvent::find($new_category->event_id);
        if (!$event) {
            throw new BLoCException("category not found");
        }

        // cek kuota kategori tujuan
        $participant_count = ArcheryEventParticipant::countEventUserBooking($category_id);
        if ($participant_count > $new_category->quota) {
            $msg = "quota kategori ini sudah penuh";
            // check kalo ada pembayaran yang pending
            $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where("event_category_id", $new_category->id)
                ->where("archery_event_participants.status", 4)
                ->where("transaction_logs.status", 4)
                ->where("transaction_logs.expired_time", ">", $time_now)
                ->where("event_id", $new_category->event_id)->count();

            if ($participant_count_pending > 0) {
                $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
            } else {
                $msg = $msg . ", silahkan daftar di kategori lain";
            }
            throw new BLoCException($msg);
        }

        // cek apakah participant tersedia atau tidak
        $participant = ArcheryEventParticipant::select("archery_event_participants.*")
            ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_participants.event_category_id")
            ->where("archery_event_participants.id", $participant_id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participants.event_id", $new_category->event_id)
            ->first();
        if (!$participant) {
            throw new BLoCException("data participant tidak tersedia");
        }

        // cek kategori saat ini
        $current_category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $participant->event_category_id)
            ->first();
        if (!$current_category) {
            throw new BLoCException("current category not found");
        }

        $user = User::find($participant->user_id);
        if (!$user) {
            throw new BLoCException("user tidak ditemukan");
        }

        if (strtolower($new_category->type) == "individual") {
            $this->changeToIndividualCategory($participant, $new_category, $user, $current_category, $event);
        } else {
            $this->changeToTeamCategoryTeam($current_category, $participant, $new_category, $user, $event);
        }

        // update participants
        $participant->event_category_id = $new_category->id;
        $participant->type = strtolower($new_category->type);
        $participant->team_category_id = $new_category->team_category_id;
        $participant->age_category_id = $new_category->age_category_id;
        $participant->competition_category_id = $new_category->competition_category_id;
        $participant->distance_id = $new_category->distance_id;
        $participant->save();

        return $participant;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer',
            'category_id' => 'required|integer'
        ];
    }

    private function changeCategoryTeam($new_category, $partticipant)
    {
        $gender_category = $new_category->gender_category;
        $time_now = time();
        // cek total pendaftar yang masih pending dan sukses
        $check_register_same_category = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
            ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where('archery_event_participants.club_id', $partticipant->club_id)
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_participants.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("archery_event_participants.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            })
            ->count();

        if ($gender_category == 'mix') {
            if ($check_register_same_category >= 3) {
                $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
                    ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where('archery_event_participants.club_id', $partticipant->club_id)
                    ->where("archery_event_participants.status", 4)
                    ->where("transaction_logs.expired_time", ">", $time_now)
                    ->count();
                if ($check_panding > 0)
                    throw new BLoCException("ada transaksi yang belum diselesaikan oleh club pada category ini");
                else
                    throw new BLoCException("club anda sudah terdaftar 2 kali di kategory ini");
            }
        } else {
            if ($check_register_same_category >= 3) {
                $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
                    ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where('archery_event_participants.club_id', $partticipant->club_id)
                    ->where("archery_event_participants.status", 4)
                    ->where("transaction_logs.expired_time", ">", $time_now)
                    ->count();
                if ($check_panding > 0)
                    throw new BLoCException("ada transaksi yang belum diselesaikan oleh club pada category ini");
                else
                    throw new BLoCException("club anda sudah terdaftar 2 kali di kategory ini");
            }
            $team_category_id = $new_category->team_category_id == "female_team" ? "individu female" : "individu male";
            $check_individu_category_detail = ArcheryEventCategoryDetail::where('event_id', $new_category->event_id)
                ->where('age_category_id', $new_category->age_category_id)
                ->where('competition_category_id', $new_category->competition_category_id)
                ->where('distance_id', $new_category->distance_id)
                ->where('team_category_id', $team_category_id)
                ->first();

            if (!$check_individu_category_detail) {
                throw new BLoCException("kategori individu untuk kategori ini tidak tersedia");
            }

            $check_participant = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail->id)
                ->where('archery_event_participants.club_id', $partticipant->club_id)
                ->count();
            if ($check_participant < (($check_register_same_category + 1) * 3)) {
                throw new BLoCException("untuk pendaftaran ke " . ($check_register_same_category + 1) . " minimal harus ada " . (($check_register_same_category + 1) * 3) . " peserta tedaftar dengan club ini");
            }
        }

        $partticipant->update([
            "event_category_id" => $new_category->id,
            "team_category_id" => $new_category->team_category_id,
            "age_category_id" => $new_category->age_category_id,
            "competition_category_id" => $new_category->competition_category_id,
            "distance_id" => $new_category->distance_id
        ]);

        return $partticipant;
    }

    private function changeToIndividualCategory(ArcheryEventParticipant $participant, ArcheryEventCategoryDetail $new_category, User $user, ArcheryEventCategoryDetail $current_category, ArcheryEvent $event)
    {
        $time_now = time();
        // cek apakah user telah mengikuti kategori tujuan atau tidak
        $is_exists_pending_or_success = ArcheryEventParticipant::leftJoin("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where("archery_event_participants.event_category_id", $new_category->id)
            ->where("archery_event_participants.user_id", $user->id)
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_participants.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("archery_event_participants.status", 4);
                    $q->where("transaction_logs.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            })->get()->count();
        if ($is_exists_pending_or_success > 0) {
            throw new BLoCException("user telah terdaftar di categori ini");
        }
        $qualification_time = ArcheryEventQualificationTime::where("category_detail_id", $new_category->id)
            ->first();
        if (!$qualification_time) {
            throw new BLoCException("waktu kualifikasi belum di set untuk kategory ini");
        }

        $team_category = ArcheryMasterTeamCategory::find($new_category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        // cek gender
        if ($user->gender == null) {
            throw new BLoCException("gender invalid");
        }
        if ($user->gender == "male") {
            if ($team_category->id == "individu female") {
                throw new BLoCException("invalid gender");
            }
        }
        if ($user->gender == "female") {
            if ($team_category->id == "individu male") {
                throw new BLoCException("invalid gender");
            }
        }

        $age_category = ArcheryMasterAgeCategory::find($new_category->age_category_id);
        if (!$age_category) {
            throw new BLoCException("age category not found");
        }

        // cek apakah usia user memenuhi persyaratan
        $check_age = ArcheryEvent::checUserAgeCanOrderCategory($user->date_of_birth, $age_category, $event);
        if ($check_age != 1) {
            throw new BLoCException($check_age);
        }

        if (strtolower($current_category->type) == "individual") {
            $participant_memmber = ArcheryEventParticipantMember::where('archery_event_participant_id', $participant->id)
                ->first();
            if (!$participant_memmber) {
                throw new BLoCException("participant member tidak tersedia");
            }

            // update schedule
            $qualification_full_day = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_memmber->id)->first();
            if (!$qualification_full_day) {
                throw new BLoCException("jadwal kualifikasi untuk user ini belum diatur");
            }
            $qualification_full_day->qalification_time_id = $qualification_time->id;
            $qualification_full_day->save();
        } else {
            // buat member
            $archery_event_participant_member = new ArcheryEventParticipantMember();
            $archery_event_participant_member->archery_event_participant_id = $participant->id;
            $archery_event_participant_member->name = $participant->name;
            $archery_event_participant_member->team_category_id = $participant->team_category_id;
            $archery_event_participant_member->user_id = $participant->user_id;
            $archery_event_participant_member->save();

            // buat schedule
            $schedule = new ArcheryEventQualificationScheduleFullDay();
            $schedule->qalification_time_id = $qualification_time->id;
            $schedule->participant_member_id  = $archery_event_participant_member->id;
            $schedule->save();

            // set point
            ArcherySeriesUserPoint::setAutoUserMemberCategory($new_category->event_id, $user->id);
        }
    }

    private function changeToTeamCategoryTeam(ArcheryEventCategoryDetail $current_category, ArcheryEventParticipant $participant, ArcheryEventCategoryDetail $new_category, User $user, ArcheryEvent $event)
    {
        $gender_category = $new_category->gender_category;
        $time_now = time();

        $club_or_city = "Club";
        if ($event->with_contingent == 1) {
            $club_or_city = "Kota";
        }

        // cek total pendaftar yang masih pending dan sukses
        $check_register_same_category = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
            ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_participants.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("archery_event_participants.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            });

        if ($event->with_contingent == 1) {
            $check_register_same_category->where('archery_event_participants.city_id', $participant->city_id);
        } else {
            $check_register_same_category->where('archery_event_participants.club_id', $participant->club_id);
        }

        $check_register_same_category = $check_register_same_category->get()->count();

        if ($gender_category == 'mix') {
            $check_success_category_mix = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
                ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where("archery_event_participants.status", 1);

            if ($event->with_contingent == 1) {
                $check_success_category_mix->where('archery_event_participants.city_id', $participant->city_id);
            } else {
                $check_success_category_mix->where('archery_event_participants.club_id', $participant->club_id);
            }

            $check_success_category_mix = $check_success_category_mix->get()->count();

            if ($check_success_category_mix >= 10) {
                throw new BLoCException($club_or_city . " anda sudah terdaftar 10 kali pada kategori ini");
            }

            $check_panding_mix = ArcheryEventParticipant::select("archery_event_participants.*")->where('archery_event_participants.event_category_id', $new_category->id)
                ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where("archery_event_participants.status", 4)
                ->where("transaction_logs.expired_time", ">", $time_now);

            if ($event->with_contingent == 1) {
                $check_panding_mix->where('archery_event_participants.city_id', $participant->city_id);
            } else {
                $check_panding_mix->where('archery_event_participants.club_id', $participant->club_id);
            }

            $check_panding_mix = $check_panding_mix->first();

            if ($check_panding_mix) {
                throw new BLoCException("terdapat pesanan yang belum di bayar oleh user dengan email " . $check_panding_mix->email);
            }

            $check_individu_category_detail_male = ArcheryEventCategoryDetail::where('event_id', $new_category->event_id)
                ->where('age_category_id', $new_category->age_category_id)
                ->where('competition_category_id', $new_category->competition_category_id)
                ->where('distance_id', $new_category->distance_id)
                ->where('team_category_id', "individu male")
                ->first();

            $check_individu_category_detail_female = ArcheryEventCategoryDetail::where('event_id', $new_category->event_id)
                ->where('age_category_id', $new_category->age_category_id)
                ->where('competition_category_id', $new_category->competition_category_id)
                ->where('distance_id', $new_category->distance_id)
                ->where('team_category_id', "individu female")
                ->first();

            if (!$check_individu_category_detail_male || !$check_individu_category_detail_female) {
                throw new BLoCException("kategori individu untuk kategori ini tidak tersedia");
            }

            $check_participant_male = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where("archery_event_participants.status", 1)
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail_male->id);

            if ($event->with_contingent == 1) {
                $check_participant_male->where('archery_event_participants.city_id', $participant->city_id);
            } else {
                $check_participant_male->where('archery_event_participants.club_id', $participant->club_id);
            }

            $check_participant_male = $check_participant_male->get()->count();

            $check_participant_female = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where("archery_event_participants.status", 1)
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail_female->id);

            if ($event->with_contingent == 1) {
                $check_participant_female->where('archery_event_participants.city_id', $participant->city_id);
            } else {
                $check_participant_female->where('archery_event_participants.club_id', $participant->club_id);
            }

            $check_participant_female = $check_participant_female->get()->count();

            if ($check_participant_male < (($check_success_category_mix + 1) * 1)) {
                throw new BLoCException("untuk pendaftaran ke " . $check_success_category_mix . " membutuhkan " . (($check_success_category_mix + 1) * 1) . " peserta laki-laki");
            }

            if ($check_participant_female < (($check_success_category_mix + 1) * 1)) {
                throw new BLoCException("untuk pendaftaran ke " . $check_success_category_mix . " membutuhkan " . (($check_success_category_mix + 1) * 1) . " peserta perempuan");
            }
        } else {
            if ($check_register_same_category >= 10) {
                $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
                    ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where("archery_event_participants.status", 4)
                    ->where("transaction_logs.expired_time", ">", $time_now);

                if ($event->with_contingent == 1) {
                    $check_panding->where('archery_event_participants.city_id', $participant->city_id);
                } else {
                    $check_panding->where('archery_event_participants.club_id', $participant->club_id);
                }

                $check_panding = $check_panding->get()->count();

                if ($check_panding > 0) {
                    throw new BLoCException("ada transaksi yang belum diselesaikan oleh " . $club_or_city . " pada category ini");
                } else {
                    throw new BLoCException($club_or_city . " anda sudah terdaftar 10 kali di kategory ini");
                }
            }
            $team_category_id = $new_category->team_category_id == "female_team" ? "individu female" : "individu male";
            $check_individu_category_detail = ArcheryEventCategoryDetail::where('event_id', $new_category->event_id)
                ->where('age_category_id', $new_category->age_category_id)
                ->where('competition_category_id', $new_category->competition_category_id)
                ->where('distance_id', $new_category->distance_id)
                ->where('team_category_id', $team_category_id)
                ->first();

            if (!$check_individu_category_detail) {
                throw new BLoCException("kategori individu untuk kategori ini tidak tersedia");
            }

            $check_participant = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail->id);

            if ($event->with_contingent == 1) {
                $check_participant->where('archery_event_participants.city_id', $participant->city_id);
            } else {
                $check_participant->where('archery_event_participants.club_id', $participant->club_id);
            }

            $check_participant = $check_participant->get()->count();

            if ($check_participant < (($check_register_same_category + 1) * 3)) {
                throw new BLoCException("untuk pendaftaran ke " . ($check_register_same_category + 1) . " minimal harus ada " . (($check_register_same_category + 1) * 3) . " peserta tedaftar dengan kontingen ini");
            }
        }

        if (strtolower($current_category->type) == "individual") {
            $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
            if (!$member) {
                throw new BLoCException("member not found");
            }

            // delete schedule
            $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $member->id)->first();
            if ($schedule) {
                $schedule->delete();
            }

            // delete series user point
            $event_series = ArcheryEventSerie::where("event_id", $current_category->event_id)->first();
            if ($event_series) {
                $series_category = ArcherySeriesCategory::where("serie_id", $event_series->serie_id)
                    ->where("age_category_id", $current_category->age_category_id)
                    ->where("competition_category_id", $current_category->competition_category_id)
                    ->where("distance_id", $current_category->distance_id)
                    ->where("team_category_id", $current_category->team_category_id)
                    ->first();

                if ($series_category) {
                    $user_point =  ArcherySeriesUserPoint::where("event_serie_id", $event_series->id)
                        ->where("user_id", $user->id)
                        ->where("event_category_id", $series_category->id)
                        ->where("member_id", $member->id)
                        ->first();

                    if ($user_point) {
                        $user_point->delete();
                    }
                }
            }

            // delete member
            $member->delete();
        }
    }
}
