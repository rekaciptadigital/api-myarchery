<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventEmailWhiteList;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ClubMember;
use App\Models\ParticipantMemberTeam;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\TemporaryParticipantMember;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\ArcherySeriesUserPoint;
use App\Models\City;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class CheckEmailIsRegister extends Transactional
{
    public function getDescription()
    {
        return "";
        /*
            # order individu
                db insert :
                            - archery_event_participants
                            - archery_event_participant_members
                            - transaction_log (for have fee)
                            - archery_event_participant_member_numbers (if free)
                            - archery_event_qualification_schedule_full_days (if free)
                            - participant_member_team (if free)
        */
    }

    protected function process($parameters)
    {
        $emails = $parameters->get("emails");
        $data = [];
        foreach ($emails as $key => $e) {
            $user = User::where("email", $e)->first();

            if ($user) {
                $data[] = (object)[
                    "data" => $user,
                    "message" => "email " . $e . " sudah terdaftar sebagai user"
                ];
            } else {
                $data[] = (object)[
                    "data" => null,
                    "message" => "email " . $e . " belum terdaftar sebagai user"
                ];
            }
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            "emails" => "required|array",
            "emails.*" => "required|email"
        ];
    }
}
