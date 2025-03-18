<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Check if user exists and has a role relationship loaded
        if (!$user || !$user->role) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the role slug is 'admin'
        if ($user->role->slug !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}