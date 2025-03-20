<?php

namespace App\Helpers;

use App\Enums\Http;
use App\Helpers\APIResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;


class ExtraExceptionHandeling
{
    /**
     * @param ValidationException $exception
     * @return Responsable
     */
    public static function handleValidationException(ValidationException $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::VALIDATION_ERROR,
            message: __('validation.validation_error'),
            errors: $exception->validator->errors()->toArray()
        );
    }

    /**
     * @param AuthenticationException $exception
     * @return Responsable
     */
    public static function handleAuthenticationException(AuthenticationException $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::UNAUTHENTICATED,
            message: __('validation.unauthenticated'),
            errors: ['token' => [__('validation.unauthenticated')]]
        );
    }

    /**
     * @param \ErrorException $exception
     * @return Responsable
     */
    public static function handleErrorException(\ErrorException $exception): Responsable
    {
        Log::error('Exception caught', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return new APIResponse(
            status: 'fail',
            code: Http::SERVER_ERROR,
            message: $exception->getMessage(),
            errors: [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]
        );
    }

    /**
     * @param ModelNotFoundException $exception
     * @return Responsable
     */
    public static function handleModelNotFoundException(ModelNotFoundException $exception): Responsable
    {
        $model = explode('\\', $exception->getMessage());

        $model = explode(']', last($model));

        return new APIResponse(
            status: 'fail',
            code: Http::NOT_FOUND,
            message: __('validation.' . ($model[0])) . ' ' . __('validation.not_found'),
            errors: ['not_found' => __('validation.' . ($model[0])) . ' ' . __('validation.not_found')]
        );
    }

    /**
     * @param AuthenticationException|AccessDeniedHttpException $exception
     * @return Responsable
     */
    public static function handleAccessDeniedHttpException(AuthenticationException|AccessDeniedHttpException $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::FORBIDDEN,
            message: __('validation.unauthorized'),
            errors: ['unauthorized' => [__('validation.unauthorized')]]
        );
    }

    /**
     * @param PostTooLargeException $exception
     * @return Responsable
     */
    public static function handlePostTooLargeException(PostTooLargeException $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::BAD_REQUEST,
            message: 'validation.payload_large'
        );
    }

    /**
     * @param NotFoundHttpException $exception
     * @return Responsable
     */
    public static function handleNotFoundException(NotFoundHttpException $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::NOT_FOUND,
            message: __('validation.not_found'),
            errors: ['data' => [__('validation.not_found')]]
        );
    }

    /**
     * @param ThrottleRequestsException $exception
     * @return Responsable
     */
    public static function handleThrottleRequestsException(ThrottleRequestsException $exception): Responsable
    {

        return new APIResponse(
            status: "fail",
            code: Http::THROTTLE,
            message: __('validation.throttle', ['seconds' => $exception->getHeaders()['Retry-After']]),
            errors: ["throttle" => [__("validation.throttle", ['seconds' => $exception->getHeaders()['Retry-After']])]]
        );
    }

    public static function handleInvalidSignatureException(InvalidSignatureException $exception): Responsable
    {

        return new APIResponse(
            status: "fail",
            code: Http::FORBIDDEN,
            message: __('validation.invalid_signature'),
            errors: ["invalid_signature" => [__("validation.invalid_signature")]]
        );

    }
}
