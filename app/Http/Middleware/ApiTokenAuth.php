<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'error' => 'API token is required'
            ], 401);
        }

        $token = str_replace('Bearer ', '', $token);

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Invalid API token'
            ], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
