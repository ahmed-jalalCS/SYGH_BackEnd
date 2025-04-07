<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LibraryStaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();

        // Check if user exists and has a role relationship loaded
        if (!$user || !$user->role) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the role slug is 'admin'
        if ($user->role->slug !== 'library_staff') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
//
//        if(!Auth::check() || !Auth::user()->hasRole('library_staff')){
//            return response(['error' => 'Unauthorized'], 403);
//        }
//        return $next($request);
    }
}
