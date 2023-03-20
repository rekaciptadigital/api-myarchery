<?php

namespace App\BLoC\Web\ClassificationMembers;

use App\Models\ChildrenClassificationMembers;
use App\Models\ParentClassificationMembers;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateClassificationChildren extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $data_req = $parameters->get('data');
        // return $data_req;
        if (!$data_req) {
            throw new BLoCException("data wajib di isi!");
        }

        foreach ($data_req as $key => $value) {
            if (empty($value['id'])) {
                throw new BLoCException("id wajib di isi!");
            }

            $check_data = ChildrenClassificationMembers::find($value['id']);

            if (!$check_data) {
                throw new BLoCException("request id :" . $value['id'] . " tidak ditemukan!");
            }

            if (empty($value['title'])) {
                throw new BLoCException("title tidak boleh kosong!");
            }
        }

        $data_id = [];
        foreach ($data_req as $key => $value) {
            $data_id[$key] = $value['id'];
            $save_data = [
                'title' => $value['title'],
                'status' => $value['status'] == true || $value['status'] == 1 || $value['status'] == null  ? 1 : 0,
            ];
            ChildrenClassificationMembers::find($value['id'])->update($save_data);
        }

        $get_all_childrens = ChildrenClassificationMembers::whereIn('id', $data_id)->get();

        return $get_all_childrens;
    }
}
