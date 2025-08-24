<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AcademicApiRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->user()?->id ?: $request->ip();
        
        // Apply different rate limits based on the endpoint
        if (str_contains($request->path(), 'analytics')) {
            // Analytics endpoints are more restrictive
            if (RateLimiter::tooManyAttempts('analytics-api:' . $key, 30)) {
                return response()->json([
                    'message' => 'Too many requests for analytics endpoints. Please try again later.',
                    'retry_after' => RateLimiter::availableIn('analytics-api:' . $key)
                ], 429);
            }
            
            RateLimiter::hit('analytics-api:' . $key, 60);
        } else {
            // General academic endpoints
            if (RateLimiter::tooManyAttempts('academic-api:' . $key, 120)) {
                return response()->json([
                    'message' => 'Too many requests for academic endpoints. Please try again later.',
                    'retry_after' => RateLimiter::availableIn('academic-api:' . $key)
                ], 429);
            }
            
            RateLimiter::hit('academic-api:' . $key, 60);
        }

        return $next($request);
    }
} 