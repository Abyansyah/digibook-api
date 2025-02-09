<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFastApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('app.fast_api_key');

        $apiKeyIsValid = (
            filled($apiKey)
            && $request->header('x-api-key') === $apiKey
        );

        if (!$apiKeyIsValid) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Invalid or missing API key'
            ], 403);
        }

        return $next($request);
    }
}
