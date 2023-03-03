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
        $childrens = $parameters->get('childrens');
        $childrensQuery = new ChildrenClassificationMembers();
        $parentQuery = new ParentClassificationMembers();
        $new_data = [];
        $parent_id = $parameters->get("id");

        $data = [
            'title' => $parameters->get("parent_title"),
            'status' => $parameters->get('status') == true || $parameters->get('status') == 1 || $parameters->get('status') == null  ? 1 : 0
        ];

        $parent = $parentQuery->where('id', '=', $parent_id)->where('deleted_at', '=', null);

        if ($parent->count() < 1) {
            throw new BLoCException("children classification tidak ditemukan!");
        }

        if (empty($parameters->get('parent_title'))) {
            throw new BLoCException("parent title tidak boleh kosong!");
        }

        if (!empty($childrens)) {
            foreach ($childrens as $value) {
                if (!$value['title']) {
                    throw new BLoCException("title di children tidak boleh kosong!");
                }

                $temp_data = [
                    'title' => $value['title'],
                    'status' => $value['status'] == true || $value['status'] == 1 || $value['status'] == null  ? 1 : 0,
                    'parent_id' => $parent_id
                ];
                array_push($new_data, $temp_data);
            }


            $childrensQuery->where('parent_id', '=', $parent_id)->delete();
            $childrensQuery->insert($new_data);
        }

        $parent = $parent->update($data);

        $result = $parentQuery->find($parent_id);
        $result->makeHidden(['admin_id', 'parent_id', 'user_id', 'deleted_at']);
        $get_childrens = $childrensQuery->where('parent_id', '=', $parent_id)->where('deleted_at', '=', null)->get();
        $get_childrens->makeHidden(['admin_id', 'parent_id', 'user_id', 'deleted_at']);
        $result['childrens'] = $get_childrens;

        return $result;
    }
}
