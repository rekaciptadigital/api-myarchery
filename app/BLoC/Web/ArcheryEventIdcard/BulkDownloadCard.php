<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventIdcardTemplate;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryClub;
use App\Models\User;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use Illuminate\Support\Facades\Auth;

class BulkDownloadCard extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $category_id = $parameters->get('event_category_id');
        $participants = ArcheryEventParticipant::where("event_category_id", $category_id)->where("status", 1)->get();
        $archery_event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$archery_event) {
            throw new BLoCException("tidak ada data tersedia");
        }

        if ($participants->isEmpty()) {
            throw new BLoCException("tidak ada partisipan");
        }

        $final_doc = [];

        $category = ArcheryEventCategoryDetail::find($category_id);
        $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);

        $idcard_event = ArcheryEventIdcardTemplate::where('event_id', $parameters->get('event_id'))->first();
        if (!$idcard_event) {
            throw new BLoCException("Template event id card tidak ditemukan");
        }
        $html_template = base64_decode($idcard_event->html_template);

        $background = $idcard_event->background;
        $logo = !empty($idcard_event->logo_event) ? $idcard_event->logo_event : "https://i.ibb.co/pXx14Zr/logo-email-archery.png";

        foreach ($participants as $participant) {
            $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();

            if (!$member) {
                throw new BLoCException("tidak ada data tersedia");
            }
            $user = User::find($member->user_id);
            if (!$user) {
                throw new BLoCException("user not found");
            }

            $number = ArcheryEventParticipantNumber::getNumber($participant->id);
            $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $member->id)->first();
            if (!$schedule) {
                throw new BLoCException("schedule not found");
            }

            $club = ArcheryClub::find($member->club);
            if (!$club) {
                $club = '';
            } else {
                $club = $club->name;
            }

            $budrest_number = $schedule && $schedule->bud_rest_number != 0 ? $schedule->bud_rest_number . $schedule->target_face : "";
            $avatar = !empty($user->avatar) ? $user->avatar : "https://i0.wp.com/eikongroup.co.uk/wp-content/uploads/2017/04/Blank-avatar.png?ssl=1";

            $final_doc[] = str_replace(
                ['{%budrest_number%}', '{%id_number%}', '{%member_name%}', '{%avatar%}', '{%event_category%}', '{%club%}', "{%background%}", '{%logo%}'],
                [$budrest_number, $number, $user->name, $avatar, $categoryLabel, $club, $background, $logo],
                $html_template
            );
        }

        $category_file = str_replace(' ', '', $categoryLabel);
        $file_name = "asset/idcard/idcard_" . $category_file . "_" . $category_id . ".pdf";
        $generate_idcard = PdfLibrary::setArrayDoc($final_doc)->setFileName($file_name)->savePdf();

        return [
            "file_name" => env('APP_HOSTNAME') . $file_name,
            "file_base_64" => env('APP_HOSTNAME') . $generate_idcard,
        ];
    }
}
