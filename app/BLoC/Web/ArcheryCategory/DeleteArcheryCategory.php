<?php

namespace App\BLoC\Web\ArcheryCategory;

use App\Models\ArcheryCategory;
use DAI\Utils\Abstracts\Transactional;

class DeleteArcheryCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        ArcheryCategory::find($parameters->get('id'))->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:archery_categories',
            ],
        ];
    }
}
