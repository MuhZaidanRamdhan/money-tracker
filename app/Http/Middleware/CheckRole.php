<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Pastikan user telah terautentikasi
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must provide a valid token to access this route.',
            ], 401);
        }

        // Periksa peran user
        if ($user->role !== $role) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not allowed to access this route.',
            ], 403);
        }

        return $next($request);
    }
}