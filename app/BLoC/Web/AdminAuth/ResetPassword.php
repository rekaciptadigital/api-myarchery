<?php
namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use App\Models\Admin;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Libraries\ForgetPassword;


class ResetPassword extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Admin::where('email', $parameters->get('email'))->first();
        if(!$admin) throw new BLoCException("Email tidak ditemukan");
        if($parameters->get('password') != $parameters->get('confirm_password')) throw new BLoCException("Konfirmasi kata sandi tidak sama, silahkan coba lagi");

        $admin->update([
            'password' => Hash::make($parameters->get('password'))
        ]);
        
        return $admin;
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
            'password' => 'required',
            'confirm_password' => 'required',
        ];
    }
}