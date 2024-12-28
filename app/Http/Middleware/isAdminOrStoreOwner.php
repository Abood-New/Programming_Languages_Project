<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isAdminOrStoreOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is an admin
        if (Auth::check() && Auth::user()->role == 'admin') {
            return $next($request);
        }

        // Check if the user is a store owner
        if (Auth::check() && Auth::user()->role == 'store_owner') {
            return $next($request);
        }

        // If neither condition is met, deny access
        return response()->json(['message' => 'Access denied'], 403);
    }
}
