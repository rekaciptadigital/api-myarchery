<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use DAI\Utils\Helpers\BLoC;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $result = BLoC::call('getUserProfile', $request->all());

            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }

    public function changePassword(Request $request)
    {
    }

    public function updateProfile(Request $request)
    {
    }

    public function updateProfileImage(Request $request)
    {
    }

    public function logout(Request $request)
    {
        try {
            $result = BLoC::call('logout', []);

            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }
}
