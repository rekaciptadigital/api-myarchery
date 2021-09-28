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
}
