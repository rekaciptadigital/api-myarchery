<?php
namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Libraries\ForgetPassword;
use App\Models\Admin;

class ValidateCodePassword extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Admin::where('email', $parameters->get('email'))->first();
        if(!$admin) throw new BLoCException("Email tidak ditemukan");

        $keyForTenMinutes =env("KEY_FORGOT_PASSWORD_PREFIX") . ":email:verify:code:10minutes:" . $parameters->get('email');
        $check_code = ForgetPassword::checkValidation($keyForTenMinutes, $parameters->get('code'));

        return $check_code;
    }

    protected function validation($parameters)
    {
        return [
            'code' => 'required',
            'email' => 'required',
        ];
    }
}