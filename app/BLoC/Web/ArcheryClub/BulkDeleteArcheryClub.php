<?php

namespace App\BLoC\Web\ArcheryClub;

use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Transactional;

class BulkDeleteArcheryClub extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $id_list = $parameters->get('ids');

        foreach ($id_list as $key => $id) {
            ArcheryClub::find($id)->delete();
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
                'exists:archery_clubs,id'
            ],
        ];
    }
}
