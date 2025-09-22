<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class RateLimitServiceProvider extends ServiceProvider
{
    protected $apiLimit;

    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('custom-api', function (Request $request) {
            $key = $request->user()?->id
                ? 'user:' . $request->user()->id
                : 'ip:' . $request->ip();

            $limit = (int) config('omni.API_LIMIT');

            return Limit::perMinute($limit)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 60;
                
                    return response()->json([
                        'message' => 'Too many requests. Please try again later.',
                        'retry_after_seconds' => (int) $retryAfter,
                    ], ResponseHTTP::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });
    }
}
