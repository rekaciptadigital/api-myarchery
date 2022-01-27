<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Libraries\ForgetPassword;
use App\Models\Admin;

class ForgotPassword extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Admin::where('email', $parameters->get('email'))->first();
        if(!$admin) throw new BLoCException("Email tidak ditemukan");

        $keyForADay = env("KEY_FORGOT_PASSWORD_PREFIX") . ":email:verify:code:day:" . $admin->email;
        $keyForTenMinutes = env("KEY_FORGOT_PASSWORD_PREFIX") . ":email:verify:code:10minutes:" . $admin->email;

        $code = ForgetPassword::getCode($keyForADay,$keyForTenMinutes,$admin);
        $send_email = ForgetPassword::setEmail($admin->email)->setName($admin->name)->setCode($code)->sendMail();
        return ["code" => 1, "msg" => "Kode sudah dikirim ke alamat email anda"];
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
        ];
    }

}
