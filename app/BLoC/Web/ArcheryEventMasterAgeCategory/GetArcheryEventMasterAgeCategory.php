<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventMasterAgeCategory;
use Illuminate\Support\Facades\Auth;

class GetArcheryEventMasterAgeCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
       
        $age_categories = ArcheryEventMasterAgeCategory::all();
    
        return $age_categories;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
