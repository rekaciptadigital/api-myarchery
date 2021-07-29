<?php

namespace App\BLoC\Web\ArcheryAgeCategory;

use App\Models\ArcheryAgeCategory;
use DAI\Utils\Abstracts\Transactional;

class EditArcheryAgeCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_age_category = ArcheryAgeCategory::find($parameters->get('id'));
        $archery_age_category->name = $parameters->get('name');
        $archery_age_category->description = $parameters->get('description');
        $archery_age_category->save();

        return $archery_age_category;
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:archery_age_categories,id',
            ],
            'name' => 'required',
            'description' => 'required',
        ];
    }
}
