<?php

namespace App\BLoC\Web\ClassificationMembers;

use App\Models\ChildrenClassificationMembers;
use App\Models\ParentClassificationMembers;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class AddClassificationChildren extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $title = $parameters->get('title');

        $status = (int)$parameters->get('status') == 1 || $parameters->get('status') == true || $parameters->get('status') == 'true' ? 1 : 0;

        $type = $parameters->get('type');

        $data = [
            'title' => $title,
            'status' => $status,
            'parent_id' => 5
        ];

        if ($type == 'from-admin') {
            if (empty($parameters->get('parent_id'))) {
                throw new BLoCException("parent classification wajib di pilih!");
            }

            $checkParent = ParentClassificationMembers::find($parameters->get('parent_id'));
            if (empty($checkParent)) {
                throw new BLoCException("parent classification tidak ditemukan!");
            }

            $data['admin_id'] = $admin->id;
            $data['parent_id'] = $parameters->get('parent_id');
        } else {
            $data['user_id'] = $admin->id;
        }

        if (empty($title)) {
            throw new BLoCException("title wajib di isi!");
        }

        $data = ChildrenClassificationMembers::create($data);
        $data->makeHidden(['admin_id', 'parent_id', 'user_id', 'deleted_at']);

        return $data;
    }
}
