<?php

namespace App\BLoC\Web\ArcheryEventQualificationTime;

use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Abstracts\Retrieval;

class GetArcheryEventQualificationTime extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $category_detail_id=$parameters->get('category_detail_id');
        
        $archery_qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category_detail_id)->get();
        
        if (!$archery_qualification_time) {
            throw new BLoCException("Data not found");
        }

        return $archery_qualification_time;
    }
    protected function validation($parameters)
    {
        return [
            "category_detail_id" => "required",
        ];
    }
}
