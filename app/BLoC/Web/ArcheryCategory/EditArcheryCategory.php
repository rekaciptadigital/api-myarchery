<?php

namespace App\BLoC\Web\ArcheryCategory;

use App\Models\ArcheryCategory;
use DAI\Utils\Abstracts\Transactional;

class EditArcheryCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_category = ArcheryCategory::find($parameters->get('id'));
        $archery_category->name = $parameters->get('name');
        $archery_category->description = $parameters->get('description');
        $archery_category->save();

        return $archery_category;
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:archery_categories,id',
            ],
            'name' => 'required',
            'description' => 'required',
        ];
    }
}
