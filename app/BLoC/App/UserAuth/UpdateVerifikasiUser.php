<?php

namespace App\BLoC\App\UserAuth;

use App\Libraries\Upload;
use App\Models\City;
use App\Models\CityCountry;
use App\Models\Country;
use App\Models\Provinces;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
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
        $ktp_kk = $parameters->get("ktp_kk");
        $is_wna = $parameters->get("is_wna");
        $name = $parameters->get("name");
        $nik = $parameters->get("nik");
        $province_id = $parameters->get("province_id");
        $city_id = $parameters->get("city_id");
        $address = $parameters->get("address");
        $passport_number = $parameters->get("passport_number");
        $country_id = $parameters->get("country_id");
        $city_of_country_id = $parameters->get("city_of_country_id");
        $passport_img = $parameters->get("passport_img");

        $user = User::find($user_id);

        if (!$user) {
            throw new BLoCException("user not found");
        }

        if ($user_login->id !== $user->id) {
            throw new BLoCException("forbiden");
        }

        if ($is_wna == 0) {
            $Validator = Validator::make($parameters->all(), [
                'nik' => [
                    Rule::unique('users')->ignore($user->id),
                ],
            ]);

            if ($Validator->fails()) {
                throw new BLoCException($Validator->errors());
            }
            
            $user->nik = $nik;

            $province = Provinces::find($province_id);
            if (!$province) {
                throw new BLoCException("Provinsi tidak tersedia");
            }
            $user->address_province_id = $province_id;

            $city = City::find($city_id);
            if (!$city) {
                throw new BLoCException("Kota tidak tersedia");
            }
            $user->address_city_id = $city_id;

            if ($ktp_kk) {
                if ($user->ktp_kk != null && $user->ktp_kk == $ktp_kk) {
                    $ktp_kk = $user->ktp_kk;
                } else {
                    Validator::make($parameters->all(), [
                        'ktp_kk' => [
                            'required',
                        ],
                    ])->validate();

                    $array_file_index_0 = explode(";", $parameters->get("ktp_kk"))[0];
                    $ext_file_upload =  explode("/", $array_file_index_0)[1];

                    if ($ext_file_upload != "jpg" && $ext_file_upload != "jpeg" && $ext_file_upload != "png") {
                        throw new BLoCException("mohon inputkan tipe data gambar png, jpeg, jpg");
                    }

                    $ktp_kk = Upload::setPath("asset/ktp_kk/")->setFileName("ktp_kk_" . $this->getRandString(4) . "_" . time())->setBase64($parameters->get('ktp_kk'))->save();
                }
                $user->ktp_kk = $ktp_kk;
            }
        } else {
            $user->passport_number = $passport_number;

            $country = Country::find($country_id);
            if (!$country) {
                throw new BLoCException("Country not found");
            }
            $user->country_id = $country_id;

            $city_of_country = CityCountry::find($city_of_country_id);
            if (!$city_of_country) {
                throw new BLoCException("city country not found");
            }
            $user->city_of_country_id = $city_of_country_id;

            if ($passport_img) {
                if ($user->passport_img != null && $user->passport_img == $passport_img) {
                    $passport_img = $user->passport_img;
                } else {
                    Validator::make($parameters->all(), [
                        'passport_img' => [
                            'required',
                        ],
                    ])->validate();

                    $array_file_index_0 = explode(";", $passport_img)[0];
                    $ext_file_upload =  explode("/", $array_file_index_0)[1];

                    if ($ext_file_upload != "jpg" && $ext_file_upload != "jpeg" && $ext_file_upload != "png") {
                        throw new BLoCException("mohon inputkan tipe data gambar png, jpeg, jpg");
                    }

                    $passport_img = Upload::setPath("asset/passport_img/")->setFileName("passport_img_" . $this->getRandString(4) . "_" . time())->setBase64($passport_img)->save();
                }
                $user->passport_img = $passport_img;
            }
        }

        $user->name = $name;
        $user->address = $address;
        $user->verify_status = 3;
        $user->is_wna = $is_wna;
        $user->save();

        return $user->getDataVerifikasiUser();
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
            'address' => 'string|string|min:1',
            "ktp_kk" => 'string',
            "nik" => 'string|min:16|max:16',
            "province_id" => "integer",
            "city_id" => "integer",
            "name" => 'string|min:1|max:200',
            "is_wna" => "required|in:0,1",
            "country_id" => "integer",
            "passport_number" => 'sometimes|required|string|unique:users,passport_number,' . $parameters->get("user_id"),
            "city_of_country_id" => "integer"
        ];
    }
}
