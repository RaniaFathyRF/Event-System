<?php

namespace App\Http\Controllers\Auth\User;

use App\Enums\Http;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Verified;
use App\Http\Requests\Auth\User\UserEmailVerificationRequest;
use App\Http\Requests\Auth\User\UserResendEmailVerificationRequest;


class EmailVerificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user/verify-email/{id}/{hash}",
     *     tags={"User - Verification"},
     *     summary="Verify user email",
     *     description="Verify the user's email address using the verification link.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         description="Hash for email verification",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *          name="expires",
     *          in="query",
     *          required=false,
     *          description="The dynamically generated expires for the URL",
     *          @OA\Schema(type="int")
     *      ),
     *      @OA\Parameter(
     *           name="signature",
     *           in="query",
     *           required=false,
     *           description="The dynamically generated signature for the URL",
     *           @OA\Schema(type="string")
     *       ),
     *      @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Email verified successfully"),
     *             @OA\Property(
     *                 property="body",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated - Invalid verification link",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="User not authenticated"),
     *             @OA\Property(property="errors", type="object", example={"verification": "User not authenticated"})
     *         )
     *     )
     * )
     */
    public function verify(UserEmailVerificationRequest $request, $id, $hash)
    {
        try {
            $user = User::find($id);
            if (!$user->hasVerifiedEmail()) {
                if ($user->markEmailAsVerified()) {
                    event(new Verified($user));
                }
            }

            return new APIResponse(
                status: 'success',
                code: Http::SUCCESS,
                message: __('validation.email_verified'),
                body: new UserResource($user)
            );
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/user/email/verification-notification",
     *     tags={"User - Verification"},
     *     summary="Resend email verification link",
     *     description="Resend the email verification link to the user's email address.",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Email of the user",
     *         required=true,
     *         @OA\Schema(type="string",format="email", example="john.doe@example.com")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Verification email sent successfully"),
     *             @OA\Property(property="body", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Email already verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Email already verified"),
     *             @OA\Property(property="errors", type="object", example={"email_already_verified": "Email already verified"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid email",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Invalid email"),
     *             @OA\Property(property="errors", type="object", example={"email": "Invalid email"})
     *         )
     *     )
     * )
     */
    public function resend(UserResendEmailVerificationRequest $request)
    {
        try {
            $data = $request->validated();
            $user = User::where('email', $data['email'])->first();
            if (!$user)
                throw new AuthenticationException();

            if ($user->hasVerifiedEmail()) {
                return new APIResponse(
                    status: 'fail',
                    code: Http::BAD_REQUEST,
                    message: __('validation.email_already_verified'),
                    errors: ['email_already_verified' => __('validation.email_already_verified')]
                );
            }

            $user->sendEmailVerificationNotification();

            return new APIResponse(
                status: 'success',
                code: Http::SUCCESS,
                message: __('validation.email_sent_successfully'),
                body: ['email_sent' => __('validation.email_sent_successfully')]
            );
        } catch (\Exception $exception) {
            throw $exception;
        }

    }
}
