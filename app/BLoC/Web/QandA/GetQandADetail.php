<?php

namespace App\BLoC\Web\QandA;

use App\Models\ArcheryEvent;
use App\Models\QandA;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetQandADetail extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $qna_id = $parameters->get('qna_id');

        $qna = QandA::where("id", $qna_id)->get();

        if($qna->isEmpty()){
            throw new BLoCException('data not found');
        }
    
        return $qna;
    }

    protected function validation($parameters)
    {
        return [
            "qna_id" => "required|integer",
        ];
    }
}
