<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()->tokenCan('admin')) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Unauthorized activity'
            ], 403);
        }
        return $next($request);
    }
}
