<?php

namespace App\Http\Middleware;

use Closure;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $userModel = JWTAuth::parseToken()->authenticate();
            $userToken = JWTAuth::parseToken()->getPayload()->get('user');

            $updatedDb = new DateTime($userModel->updated_security);
            $updatedToken = new DateTime($userToken['updated_security']);

            if ($updatedDb > $updatedToken) {
                return response()->error(['Terdapat perubahan pengaturan keamanan, silahkan login ulang'], 403);
            }
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->error(['Token yang anda gunakan tidak valid'], 401);
            } elseif ($e instanceof TokenExpiredException) {
                return response()->error(['Token anda telah kadaluarsa, silahkan login ulang'], 403);
            } else {
                return response()->error(['Silahkan login terlebih dahulu. ' . $e->getMessage()], 403);
            }
        }

        return $next($request);
    }
}
