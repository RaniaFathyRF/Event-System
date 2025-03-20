<?php

namespace App\Http\Middleware;

use App\Enums\Http;
use App\Helpers\APIResponse;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                !$request->user()->hasVerifiedEmail())) {
            return new APIResponse(
                status: 'fail',
                code: Http::NOT_VERIFIED,
                message: __('validation.email_not_verified'),
                errors: ['not_verified' => __('validation.email_not_verified')]);

        }

        return $next($request);
    }
}
