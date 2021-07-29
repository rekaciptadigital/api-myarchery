<?php

namespace App\BLoC\Web\ArcheryCategory;

use App\Models\ArcheryCategory;
use DAI\Utils\Abstracts\Transactional;

class AddArcheryCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_category = new ArcheryCategory();
        $archery_category->name = $parameters->get('name');
        $archery_category->description = $parameters->get('description');
        $archery_category->save();

        return $archery_category;
    }

    protected function validation($parameters)
    {
        return [
            'name' => 'required',
            'description' => 'required',
        ];
    }
}
