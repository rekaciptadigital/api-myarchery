<?php

namespace App\Http\Controllers;

use DAI\Utils\Helpers\BLoC;
use DAI\Utils\Traits\ApiResponse;
use DAI\Utils\Traits\FileHandler;
use Exception;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ApiResponse;
    use FileHandler;

    public function execute($function, $params = [])
    {
        try {
            $result = BLoC::call($function, $params);
            return $this::success($result);
        } catch (Exception $e) {
            return $this::failed($e);
        }
    }

    public function display(Request $request)
    {
        return $this->viewFile($request->file_path);
    }

    public function download(Request $request)
    {
        return $this->downloadFile($request->file_path);
    }
}
