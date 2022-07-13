<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class Password extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin_login = Auth::user();
        $admin = Admin::where("id",$admin_login->id)->first();
        if (!Hash::check($parameters->get('password'), $admin->password)) {
            throw new BLoCException("password lama salah");
        }
        $admin->update([
            'password' => Hash::make($parameters->get('password'))
        ]);
        return ["updated" => true];
    }

    protected function validation($parameters)
    {
        return ['password' => 'required|string|min:6|confirmed'];
    }
}
