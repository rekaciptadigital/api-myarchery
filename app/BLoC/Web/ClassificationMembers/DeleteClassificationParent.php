<?php

namespace App\BLoC\Web\ClassificationMembers;

use App\Models\ChildrenClassificationMembers;
use App\Models\ParentClassificationMembers;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class DeleteClassificationParent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $parent = ParentClassificationMembers::where('id', '=', $parameters->get("id"));

        if ($parent->count() < 1) {
            throw new BLoCException("parent classification tidak ditemukan!");
        }


        ChildrenClassificationMembers::where('parent_id', '=', $parameters->get('id'))->delete();

        $parent->delete();

        return "berhasil menghapus data parent";
    }
}
