<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use DAI\Utils\Helpers\BLoC;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->all();
            $result = BLoC::call('login', $credentials);
            if (!$result['access_token']) {

                return $this::unauthorized(__('response.401'));
            }

            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }

    public function register(Request $request)
    {
        try {
            $result = BLoC::call('register', $request->all());

            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $result = BLoC::call('resetPassword', $request->all());

            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $result = BLoC::call('forgotPassword', $request->all());

            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }
}
