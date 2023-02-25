<?php

namespace App\BLoC\Web\ClassificationMembers;

use App\Models\ChildrenClassificationMembers;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetChildrenClassification extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 10;
        $id_parent = $parameters->get("parent_id");
        $type = $parameters->get("type");

        $result = ChildrenClassificationMembers::where('deleted_at', '=', null)
            ->where('status', '=', 1);

        if ($type == 'with-parent') {
            if (empty($id_parent)) {
                throw new BLoCException("id parent wajib di isi!");
            }

            $result = $result->where('parent_id', '=', $id_parent);
        } elseif ($type == 'from-member') {
            $result = $result->where('admin_id', '=', null)
                ->where('user_id', '!=', null)
                ->where('parent_id', '=', 5);
        } else {
            throw new BLoCException("type wajib di isi!");
        }

        $result = $result->paginate($limit);
        $result->makeHidden(['admin_id', 'parent_id', 'user_id', 'deleted_at']);

        return $result;
    }
}
