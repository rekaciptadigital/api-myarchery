<?php

namespace App\Http\Middleware;

use Closure;
use DAI\Utils\Traits\ApiResponse;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Models\UserLoginToken;

class UserAuthenticate
{
    use ApiResponse;
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard('app-api')->guest()) {
            return $this::unauthorized();
        }
        $private_signature = $this->auth->payload()["jti"];
        $check_private_signature = UserLoginToken::where("private_signature",$private_signature)->first();
        if(!$check_private_signature)
            return $this::unauthorized();

        return $next($request);
    }
}
