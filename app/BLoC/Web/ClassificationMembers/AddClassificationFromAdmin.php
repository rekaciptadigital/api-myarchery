<?php

namespace App\BLoC\Web\ClassificationMembers;

use App\Models\ChildrenClassificationMembers;
use App\Models\ParentClassificationMembers;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class AddClassificationFromAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $get_childrens = empty($parameters->get('childrens')) ? [] : json_decode($parameters->get('childrens'), true);

        if (empty($parameters->get("parent_title"))) {
            throw new BLoCException("title parent wajib di isi!");
        }

        $checkParentClassification = ParentClassificationMembers::where('title', '=', $parameters->get('parent_title'))
            ->where('admin_id', $admin->id)
            ->where('status', '=', 1)
            ->count();

        if ($checkParentClassification > 0) {
            throw new BLoCException("title parent sudah pernah di isi oleh admin!");
        }

        $data_add_parent = [
            'title' =>  $parameters->get('parent_title'),
            'status' => $parameters->get('status') == true || $parameters->get('status') == 1 || $parameters->get('status') == null  ? 1 : 0,
            'admin_id' => $admin->id
        ];

        $parent_classification = ParentClassificationMembers::create($data_add_parent);


        if (count($get_childrens) < 2) {
            $parent_classification->delete();
            throw new BLoCException("children classification minimal 2!");
        }

        $data_childrens = [];
        foreach ($get_childrens as $key_origin => $value_origin) {
            if (empty($value_origin['title'])) {
                $parent_classification->delete();
                throw new BLoCException("title di children classification ada yang kosong!");
            }

            $checkChildrenTitle = ChildrenClassificationMembers::where('title', '=', $value_origin['title'])
                ->where('admin_id', $admin->id)
                ->where('status', '=', 1)
                ->count();

            if ($checkParentClassification > 0) {
                throw new BLoCException("title children sudah pernah di isi oleh admin!");
            }


            $status = 0;
            if ($value_origin['status'] == true || $value_origin['status'] == 1 || $value_origin['status'] == null) {
                $status = 1;
            }

            array_push($data_childrens, [
                'title' => $value_origin['title'],
                'status' => $status,
                'parent_id' => $parent_classification->id,
                'admin_id' => $admin->id
            ]);
        }

        ChildrenClassificationMembers::insert($data_childrens);

        $result = ChildrenClassificationMembers::where('parent_id', $parent_classification->id)->get();

        $result->makeHidden(['admin_id', 'parent_id', 'user_id', 'deleted_at']);
        return $result;
    }
}
