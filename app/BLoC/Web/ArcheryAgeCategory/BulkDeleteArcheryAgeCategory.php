<?php

namespace App\BLoC\Web\ArcheryAgeCategory;

use App\Models\ArcheryAgeCategory;
use DAI\Utils\Abstracts\Transactional;

class BulkDeleteArcheryAgeCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $id_list = $parameters->get('ids');

        foreach ($id_list as $key => $id) {
            ArcheryAgeCategory::find($id)->delete();
        }
        return [];
    }

    protected function validation($parameters)
    {
        return [
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'exists:archery_age_categories,id'
            ],
        ];
    }
}
