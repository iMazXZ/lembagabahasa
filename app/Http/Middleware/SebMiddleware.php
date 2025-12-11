<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SebMiddleware
{
    /**
     * Handle an incoming request.
     * Validates that the request comes from Safe Exam Browser.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->userAgent();

        // Check if User-Agent contains "SEB" (Safe Exam Browser)
        if (!str_contains($userAgent ?? '', 'SEB')) {
            // For development/testing, allow bypass with query param
            if (app()->environment('local') && $request->query('seb_bypass') === 'true') {
                return $next($request);
            }

            return response()->view('toefl.seb-required', [], 403);
        }

        return $next($request);
    }
}
