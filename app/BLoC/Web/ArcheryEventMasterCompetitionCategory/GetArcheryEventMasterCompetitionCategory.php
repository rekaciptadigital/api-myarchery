<?php

namespace App\BLoC\Web\ArcheryEventMasterCompetitionCategory;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventMasterCompetitionCategory;
use Illuminate\Support\Facades\Auth;

class GetArcheryEventMasterCompetitionCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
       
        $competition_categories = ArcheryEventMasterCompetitionCategory::where("is_hide","0")->get();
    
        return $competition_categories;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
