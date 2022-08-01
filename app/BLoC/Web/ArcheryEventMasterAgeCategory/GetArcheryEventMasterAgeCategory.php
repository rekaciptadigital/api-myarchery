<?php

namespace App\BLoC\Web\ArcheryEventMasterAgeCategory;


use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryMasterAgeCategory;
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
       
        $age_categories = ArcheryMasterAgeCategory::all();
    
        return $age_categories;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
