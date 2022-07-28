<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\ArcheryEventOrganizer;

class Login extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $params = ['email' => $parameters->get("email"), 'password' => $parameters->get("password")];
        $token = Auth::setTTL(60 * 24 * 7)->attempt($params);
        $error_message = "Password salah";
        if (!$token) {
            $admin = Admin::where("email", $parameters->get("email"))->first();
            if (!$admin) {
                $error_message = "Email anda belum terdaftar";
            }
            throw new BLoCException($error_message);
        }

        $admin = Auth::user();

        // update eo_id for archery enterprise
        if ($parameters->get("login_from") == "enterprise" && $admin->eo_id == 0) {
            $archery_event_organizer = new ArcheryEventOrganizer();
            $archery_event_organizer->eo_name = $admin->name;
            $archery_event_organizer->save();
            $admin->update([
                'eo_id' => $archery_event_organizer->id
            ]);
        }

        return [
            'profile' => $admin,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Admin::getProfile()
        ];
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }
}
