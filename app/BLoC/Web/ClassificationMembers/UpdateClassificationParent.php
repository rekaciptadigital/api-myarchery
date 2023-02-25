<?php

namespace App\BLoC\Web\ClassificationMembers;

use App\Models\ChildrenClassificationMembers;
use App\Models\ParentClassificationMembers;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateClassificationParent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $data = [
            'title' => $parameters->get('title'),
            'status' => (int)$parameters->get('status') == 1 || $parameters->get('status') == true || $parameters->get('status') == 'true' ? 1 : 0
        ];


        $parent = ParentClassificationMembers::where('id', '=', $parameters->get("id"));

        if ($parent->count() < 1) {
            throw new BLoCException("children classification tidak ditemukan!");
        }

        if (empty($parameters->get('title'))) {
            throw new BLoCException("title tidak boleh kosong!");
        }

        $parent = $parent->update($data);

        $result = ParentClassificationMembers::find($parameters->get('id'));
        $result->makeHidden(['admin_id', 'parent_id', 'user_id', 'deleted_at']);

        return $result;
    }
}
