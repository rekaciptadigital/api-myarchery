<?php

namespace App\BLoC\Web\ArcheryCategory;

use App\Models\ArcheryCategory;
use DAI\Utils\Abstracts\Retrieval;

class FindArcheryCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_category = ArcheryCategory::find($parameters->get('id'));

        return $archery_category;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_categories,id',
        ];
    }
}