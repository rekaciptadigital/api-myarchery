<?php

namespace App\BLoC\Web\ArcheryAgeCategory;

use App\Models\ArcheryAgeCategory;
use DAI\Utils\Abstracts\Retrieval;

class FindArcheryAgeCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_age_category = ArcheryAgeCategory::find($parameters->get('id'));

        return $archery_age_category;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_age_categories,id',
        ];
    }
}