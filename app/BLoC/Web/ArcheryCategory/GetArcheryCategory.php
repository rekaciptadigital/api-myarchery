<?php

namespace App\BLoC\Web\ArcheryCategory;

use App\Models\ArcheryCategory;
use DAI\Utils\Abstracts\Retrieval;

class GetArcheryCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_categories = ArcheryCategory::all();

        return $archery_categories;
    }
}
