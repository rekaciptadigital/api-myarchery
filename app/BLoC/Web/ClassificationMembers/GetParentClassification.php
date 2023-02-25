<?php

namespace App\BLoC\Web\ClassificationMembers;

use App\Models\ParentClassificationMembers;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetParentClassification extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 10;

        $result = ParentClassificationMembers::where('deleted_at', '=', null)
            ->where('status', '=', 1)
            ->whereNotIn('title', ['Dari Peserta'])
            ->where(function ($query) use ($admin) {
                $query->where('admin_id', '=', null)
                    ->orWhere('admin_id', '=', $admin->id);
            })
            ->paginate($limit);
        $result->makeHidden(['admin_id', 'deleted_at']);
        return $result;
    }
}
