<?php

namespace App\BLoC\App\UserAuth;

use App\Libraries\Upload;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UpdateVerifikasiUser extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();

        $user_id = $parameters->get('user_id');
        $user = User::find($user_id);

        if (!$user) {
            throw new BLoCException("user not found");
        }

        if ($user_login->id !== $user->id) {
            throw new BLoCException("forbiden");
        }

        if ($user->verify_status == 4 || $user->verify_status == 3 || $user->verify_status == 2) {
            if ($parameters->get('ktp_kk')) {
                $ktp_kk = Upload::setPath("asset/ktp_kk/")->setFileName("ktp_kk_" . $this->getRandString(4) . "_" . time())->setBase64($parameters->get('ktp_kk'))->save();
                $user->ktp_kk = $ktp_kk;
            }

            Validator::make($parameters->all(), [
                'email' => [
                    'nik',
                    Rule::unique('users')->ignore($user->id),
                ],
            ])->validate();
            $user->nik = $parameters->get('nik');

            $user->name = $parameters->get('name');

            $user->address = $parameters->get('address');

            $user->address_province_id = $parameters->get('province_id');

            $user->address_city_id = $parameters->get('city_id');

            $user->verify_status = 3;

            $user->save();
        } else {
            throw new BLoCException("User telah terverifikasi");
        }

        return $user;
    }

    private function getRandString($total)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $total; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    protected function validation($parameters)
    {
        return [
            "user_id" => 'required|integer',
            'address' => 'required|string',
            "ktp_kk" => 'string',
            "nik" => 'required|string|min:16|max:16',
            "province_id" => "required|integer",
            "city_id" => "required|integer",
            "name" => 'required|string'
        ];
    }
}
