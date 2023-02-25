<?php

namespace App\BLoC\Web\ClassificationMembers;

use App\Models\ChildrenClassificationMembers;
use App\Models\ParentClassificationMembers;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class DeleteClassificationChildren extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $children = ChildrenClassificationMembers::where('id', '=', $parameters->get("id"));

        if ($children->count() < 1) {
            throw new BLoCException("children classification tidak ditemukan!");
        }

        $children->delete();

        return "berhasil menghapus data children classification";
    }
}
