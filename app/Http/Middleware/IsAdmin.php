<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user login dan punya role admin
        if (! $request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Hanya admin yang dapat mengakses.'], 403);
        }

        return $next($request);
    }
}
