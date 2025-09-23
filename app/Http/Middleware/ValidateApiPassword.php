<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiPassword
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks for an API password in the request headers.
     * It supports plain-text comparison or bcrypt-check if the stored
     * API_PASSWORD in the environment looks like a bcrypt hash.
     *
     * Header names checked (in order): X-API-PASSWORD, Api-Password
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $headerPassword = $request->header('X-API-PASSWORD') ?: $request->header('Api-Password');

        if (empty($headerPassword)) {
            return response()->json(['error' => 'API password required'], Response::HTTP_UNAUTHORIZED);
        }

        $envPassword = env('API_PASSWORD');

        if (empty($envPassword)) {
            // Misconfiguration: API_PASSWORD not set
            return response()->json(['error' => 'Server misconfiguration'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Timing-safe direct comparison for plain-text config
        $ok = hash_equals((string) $envPassword, (string) $headerPassword);


        if (! $ok) {
            return response()->json(['error' => 'Invalid API password'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
