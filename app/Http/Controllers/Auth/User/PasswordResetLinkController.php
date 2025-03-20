<?php

namespace App\Http\Controllers\Auth\User;

use App\Enums\Http;
use App\Http\Requests\Auth\User\PasswordResetLinkRequest;
use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;


class PasswordResetLinkController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/user/forgot-password",
     *     summary="Send a password reset link",
     *     tags={"User - Verification"},
     *     @OA\Parameter(
     *           name="email",
     *           in="query",
     *           description="email of the user",
     *           required=true,
     *           @OA\Schema(type="string",format="email",example="user@example.com")
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="We have emailed your password reset link!"),
     *             @OA\Property(property="body", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The email field is required."),
     *             @OA\Property(property="errors", type="object", example={"email": "The email field is required."})
     *         )
     *     )
     * )
     */
    public function send(PasswordResetLinkRequest $request)
    {
        try {
            // Validate the email
            $data = $request->validated();

            // Send the password reset link
            $status = Password::sendResetLink($data);

            // Handle the response
            if ($status === Password::RESET_LINK_SENT) {
                return new APIResponse(
                    status: 'success',
                    code: Http::SUCCESS,
                    message: __($status),
                    body: []
                );
            }
            return new APIResponse(
                status: 'fail',
                code: Http::VALIDATION_ERROR,
                message: __($status),
                errors: ['reset_link' => __($status)]
            );
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
