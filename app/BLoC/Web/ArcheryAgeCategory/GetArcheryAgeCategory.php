<?php

namespace App\BLoC\Web\ArcheryAgeCategory;

use App\Models\ArcheryAgeCategory;
use DAI\Utils\Abstracts\Retrieval;

class GetArcheryAgeCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_age_categories = ArcheryAgeCategory::all();

        return $archery_age_categories;
    }
}
