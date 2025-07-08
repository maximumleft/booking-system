<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return response()->json([
                'error' => 'API token is required'
            ], 401);
        }

        // Убираем 'Bearer ' если есть
        $token = str_replace('Bearer ', '', $token);

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Invalid API token'
            ], 401);
        }

        // Добавляем пользователя в request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
