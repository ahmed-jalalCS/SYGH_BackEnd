<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        if ( Auth::check() && Auth::user()->role->slug =='admin') {
            return $next($request);
        }
        else
        {
            return response()->json([
                'status' => 'error',
                'message' => 'لست مصرح للوصول'
            ],403);
        }

        // Check if the user is authenticated and is an admin

//        if (!Auth::check() || !Auth::user()->role) {
//        }

//        if(Auth::user()){
//            return response(['error' => 'Unauthorized'], 403);
//        }
//        return $next($request);


    }
}
