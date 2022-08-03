<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class BookingTemporary extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_id = $parameters->get("category_id");
        $user = Auth::guard('app-api')->user();
        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $participant = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $category, 6, 0, null, strtotime(env("EXPIRED_BOOKING_TIME", "+15 minutes"), time()));

        return [
            "participant_id" => $participant->id,
            "category_id" => $category_id,
            "expired_booking_time" => $participant->expired_booking_time
        ];
    }

    protected function validation($parameters)
    {
        return [
            'category_id' => 'required',
        ];
    }
}
