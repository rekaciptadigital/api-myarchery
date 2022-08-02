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
        $participant = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), null, $category, 6, 0, null);
    }

    protected function validation($parameters)
    {
        return [
            'category_id' => 'required',
        ];
    }
}
