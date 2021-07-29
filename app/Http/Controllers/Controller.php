<?php

namespace App\Http\Controllers;

use DAI\Utils\Helpers\BLoC;
use DAI\Utils\Traits\ApiResponse;
use Exception;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ApiResponse;

    public function execute($function, $params = []) {
        try {
            $result = BLoC::call($function, $params);
            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }
}
