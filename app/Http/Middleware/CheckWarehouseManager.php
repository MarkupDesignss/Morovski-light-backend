<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckWarehouseManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Check role_users table (role_id = 2)
        $hasRole = DB::table('role_users')
            ->where('user_id', $user->id)
            ->where('role_id', 2)
            ->exists();

        if (!$hasRole) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied. Only Warehouse Manager allowed.'
            ], 403);
        }

        return $next($request);
    }
}
