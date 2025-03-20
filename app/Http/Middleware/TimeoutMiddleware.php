<?php

namespace App\Http\Middleware;

use App\Enums\Http;
use App\Helpers\APIResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start timing the request
        $start = microtime(true);

        // Set a timeout limit in seconds
        $timeoutLimit = env('API_TIMEOUT', 30);

        // Process the request
        $response = $next($request);

        // Calculate the duration
        $duration = microtime(true) - $start;

        // Check if the duration exceeds the timeout limit
        if ($duration > $timeoutLimit) {
            return new APIResponse(
                status: 'fail',
                code: Http::TIMEOUT_ERROR,
                message: __('validation.time_out'),
                errors: ['timeout' => [__('validation.time_out')]]
            );
        }

        return $response;
    }
}

