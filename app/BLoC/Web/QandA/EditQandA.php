<?php

namespace App\BLoC\Web\QandA;

use App\Models\QandA;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class EditQandA extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        
        $qna = QandA::find($parameters->get('id'));

        if(!$qna){
            throw new BLoCException('data not found');
        }

        $qna->event_id = $parameters->get('event_id');
        $qna->sort = $parameters->get('sort');
        $qna->question = $parameters->get('question');
        $qna->answer = $parameters->get('answer');
        $qna->is_hide = $parameters->get('is_hide');
        $qna->save();

        return $qna;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required",
        ];
    }
}
