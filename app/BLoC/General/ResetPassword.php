<?php
namespace App\BLoC\General;

use DAI\Utils\Abstracts\Transactional;

class ResetPassword extends Transactional
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
            'token' => 'required',
            'password' => 'required',
        ];
    }
}
