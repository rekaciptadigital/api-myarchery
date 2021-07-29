<?php

namespace App\BLoC\Web\ArcheryAgeCategory;

use App\Models\ArcheryAgeCategory;
use DAI\Utils\Abstracts\Transactional;

class DeleteArcheryAgeCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        ArcheryAgeCategory::find($parameters->get('id'))->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:archery_age_categories',
            ],
        ];
    }
}
