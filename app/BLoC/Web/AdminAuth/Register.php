<?php

namespace App\BLoC\Web\AdminAuth;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\City;
use App\Models\Provinces;
use App\Models\Role;
use App\Models\ArcheryEventOrganizer;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Jobs\RegisterSuccessEmailJob;
use Queue;
use App\Models\AdminNotifTopic;

class Register extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $intro = $parameters->get("intro");
        $province_id = $parameters->get("province_id");
        $city_id = $parameters->get("city_id");
        $province = Provinces::find($province_id);
        if (!$province) {
            throw new BLoCException("province not found");
        }

        $city = City::where("province_id", $province_id)->where("id", $city_id)->first();
        if (!$city) {
            throw new BLoCException("city not valid");
        }
        $admin = Admin::create([
            'name' => $parameters->get('name_organizer'),
            'email' => $parameters->get('email'),
            'password' => Hash::make($parameters->get('password')),
            'province_id' => $province_id,
            "city_id" => $city_id,
            'phone_number' => $parameters->get('phone_number'),
            "intro" => json_encode($intro)
        ]);
        AdminNotifTopic::saveTopic("ADMIN_" . $admin->id, $admin->id);
        $role = Role::where('name', 'event_organizer')->first();

        $admin_role = new AdminRole();
        $admin_role->admin_id = $admin->id;
        $admin_role->role_id = !is_null($role) ? $role->id : null;
        $admin_role->save();

        $archery_event_organizer = new ArcheryEventOrganizer();
        $archery_event_organizer->eo_name = $admin->name;
        $archery_event_organizer->save();
        $admin->update([
            'eo_id' => $archery_event_organizer->id
        ]);

        // send email registration success
        // $this->sendMail($admin); // email content not ready yet

        $token = Auth::setTTL(60 * 24 * 7)->attempt([
            'email' => $parameters->get('email'),
            'password' => $parameters->get('password'),
        ]);
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::factory()->getTTL()
        ];
    }

    protected function validation($parameters)
    {
        return [
            'name_organizer' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => "required|unique:admins",
            'province_id' => "required",
            "city_id" => "required",
            "intro" => "required"
        ];
    }

    private function sendMail($admin)
    {
        $data = [
            'email' => $admin->email,
            'name' => $admin->name,
            'type' => 'admin'
        ];
        return Queue::push(new RegisterSuccessEmailJob($data));
    }
}
