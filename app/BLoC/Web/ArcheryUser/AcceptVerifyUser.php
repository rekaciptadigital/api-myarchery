<?php

namespace App\BLoC\Web\ArcheryUser;

use App\Models\ArcheryEvent;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Abstracts\Retrieval;

class AcceptVerifyUser extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

    }

    protected function validation($archery_event)
    {
        return [
            
        ];
    }
}