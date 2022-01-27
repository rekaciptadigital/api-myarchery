<?php

namespace App\BLoC\Web\ArcheryEventMasterTeamCategory;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventMasterTeamCategory;
use Illuminate\Support\Facades\Auth;

class GetArcheryEventMasterTeamCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
       
        $team_categories = ArcheryEventMasterTeamCategory::where('is_hide', 0)->orderBy('short','asc')->get();
    
        return $team_categories;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
