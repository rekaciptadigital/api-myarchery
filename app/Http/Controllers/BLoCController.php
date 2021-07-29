<?php

namespace App\Http\Controllers;

use DAI\Utils\Helpers\BLoC;
use DAI\Utils\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use ReflectionClass;

class BLoCController extends BaseController
{
    use ApiResponse;

    public function execute(Request $request) {
        try {
            $params = $request->all();
            $bloc_name = $request->bloc_name;
            unset($params['bloc_name']);
            $result = BLoC::call($bloc_name, $params);
            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }
}
