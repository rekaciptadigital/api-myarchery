<?php

namespace App\BLoC\Web\ArcheryEventMasterDistanceCategory;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventMasterDistance;
use Illuminate\Support\Facades\Auth;

class GetArcheryEventMasterDistanceCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
       
        $distance_categories = ArcheryEventMasterDistance::all();
    
        return $distance_categories;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
