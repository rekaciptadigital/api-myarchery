<?php

namespace App\BLoC\Web\ArcheryCategory;

use App\Models\ArcheryCategory;
use DAI\Utils\Abstracts\Transactional;

class BulkDeleteArcheryCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $id_list = $parameters->get('ids');

        foreach ($id_list as $key => $id) {
            ArcheryCategory::find($id)->delete();
        }
        return [];
    }

    protected function validation($parameters)
    {
        return [
            'ids' => [
                'required',
                'array'
            ],
            'ids.*' => [
                'exists:archery_categories,id'
            ],
        ];
    }
}
