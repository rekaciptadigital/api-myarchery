<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GetArcheryScoring extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $limit  = !empty($parameters->get('limit')) ? $parameters->get('limit') : 10;
        $page   = $parameters->get('page');
        $offset = ($page - 1) * $limit;
        $type   = $parameters->get('type');

        $archery_scorings = ArcheryScoring::where(function ($query) use ($type){
                                if (!is_null($type)) {
                                    $query->where('type', $type);
                                }
                            })->orderBy('id', 'ASC')->limit($limit)->offset($offset)->get();
        if (!$archery_scorings) {
            throw new BLoCException("data not found");
        }

        return $archery_scorings;
    }

    protected function validation($parameters)
    {
        return [
            'page' => 'min:1',
            'limit' => 'numeric|min:1|max:50'
        ];
    }
}