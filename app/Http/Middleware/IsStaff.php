<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user login dan punya role staff
        if (! $request->user() || $request->user()->role !== 'staff') {
            return response()->json(['message' => 'Akses ditolak. Hanya staff yang dapat mengakses.'], 403);
        }

        return $next($request);
    }
}
