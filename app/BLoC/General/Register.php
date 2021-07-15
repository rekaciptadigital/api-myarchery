<?php
namespace App\BLoC\General;

use DAI\Utils\Abstracts\Transactional;

class Register extends Transactional
{
    public function getDescription()
    {
    }

    protected function prepare($params, $original_params)
    {
        return $params;
    }

    protected function process($params, $original_params)
    {
        return $params;
    }

    protected function rules()
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }
}
