<?php

namespace App\Http\Middleware;

use Closure;
use DAI\Utils\Helpers\CaseConvert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiGate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        if(env("MAINTENANCE_MODE",false)){
            $ip = $this->get_client_ip();
            $allow = explode("|",env("ALLOWED_IP_MAINTENANCE"));
            if(!in_array($ip,$allow)){
                return response()->json(["message"=>"maintenance", "ip" => $ip], 503, $headers);
            }
        }
        
        $lang = ($request->hasHeader('Accept-Language')) ? $request->header('Accept-Language') : 'en';

        app('translator')->setLocale($lang);

        $request_data = $request->all();
        $converted_request_data = CaseConvert::snake($request_data);
        $request->merge($converted_request_data);
        $response = $next($request);

        if ($response instanceof IlluminateResponse) {
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }
        }

        if ($response instanceof SymfonyResponse) {
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
        }

        if ($response instanceof JsonResponse) {
            $current_data = $response->getData();
            $current_data = CaseConvert::camel($current_data);
            $response->setData($current_data);
        }
        return $response;
    }

    
// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
}
