<?php
namespace App\BLoC\App\UserAuth;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Libraries\ForgetPassword;
use App\Models\User;

class UserResetPassword extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = User::where('email', $parameters->get('email'))->first();
        if(!$user) throw new BLoCException("Email tidak ditemukan");
        if($parameters->get('password') != $parameters->get('confirm_password')) throw new BLoCException("Konfirmasi kata sandi tidak sama, silahkan coba lagi");

        $keyForTenMinutes = "email:verify:code:10minutes:" . $parameters->get('email');
        $check_code = ForgetPassword::checkValidation($keyForTenMinutes, $parameters->get('code'));
        if($check_code == true) {
            $user->update([
                'password' => Hash::make($parameters->get('password'))
            ]);
        }

        return $user;
    }

    protected function validation($parameters)
    {
        return [
            'code' => 'required',
            'email' => 'required',
            'password' => 'required',
            'confirm_password' => 'required',
        ];
    }
}
