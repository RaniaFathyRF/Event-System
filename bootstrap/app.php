<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TimeoutMiddleware;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Helpers\ExtraExceptionHandeling;
use Illuminate\Routing\Exceptions\InvalidSignatureException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // schedule sync ticket twice a day
        $schedule->command('sync:tickets')->everySixHours(); // Adjust frequency as needed

    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
           // EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'timeout' => TimeoutMiddleware::class,
            'verified' => EnsureEmailIsVerified::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // add custom handling for different exceptions types
        $exceptionHandlers = [
            AuthenticationException::class => 'handleAuthenticationException',
            ModelNotFoundException::class => 'handleModelNotFoundException',
            AuthorizationException::class => 'handleAccessDeniedHttpException',
            AccessDeniedHttpException::class => 'handleAccessDeniedHttpException',
            PostTooLargeException::class => 'handlePostTooLargeException',
            ThrottleRequestsException::class => 'handleThrottleRequestsException',
            ValidationException::class => 'handleValidationException',
            NotFoundHttpException::class => 'handleNotFoundException',
            ErrorException::class => 'handleErrorException',
            InvalidSignatureException::class => 'handleInvalidSignatureException',
        ];
        collect($exceptionHandlers)->map(function ($callback, $exceptionClass) use ($exceptions) {

            $exceptions->render(function (Exception $exception, Request $request) use ($exceptionClass, $callback) {

                if ($request->is('api/*')) {

                    if ($exception instanceof $exceptionClass) {

                        return ExtraExceptionHandeling::$callback($exception);
                    }
                }
            });
        });
    })->create();
