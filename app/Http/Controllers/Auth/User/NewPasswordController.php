<?php

namespace App\Http\Controllers\Auth\User;

use App\Enums\Http;
use App\Http\Requests\Auth\User\NewPasswordRequest;
use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;


class NewPasswordController extends Controller
{

    /**
     * Reset User Password
     *
     * @OA\Post(
     *     path="/api/user/reset-password",
     *     summary="Reset the user's password",
     *     tags={"User - Verification"},
     *     @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="email of the user",
     *          required=true,
     *          @OA\Schema(type="string",format="email",example="user@example.com")
     *      ),
     *     @OA\Parameter(
     *          name="password",
     *          in="query",
     *          description="password",
     *          required=true,
     *          @OA\Schema(type="string", format="password", example="newStrongPassword123")
     *      ),
     *     @OA\Parameter(
     *          name="password_confirmation",
     *          in="query",
     *          description="Password Confirmation",
     *          required=true,
     *          @OA\Schema(type="string", format="password", example="newStrongPassword123")
     *      ),
     *     @OA\Parameter(
     *          name="token",
     *          in="query",
     *          description="token",
     *          required=true,
     *          @OA\Schema(type="string", example="reset_token_here")
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Your password has been reset.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Your password has been reset."),
     *             @OA\Property(property="body", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Invalid token or email"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="Invalid token or email"))
     *             )
     *         )
     *     )
     * )
     */
    public function reset(NewPasswordRequest $request)
    {
        try {

            // Validate the request
            $data = $request->validated();

            // Prepare the credentials array
            $credentials = [
                'email' => $data['email'],
                'password' =>$data['password'],
                'password_confirmation' => $data['password_confirmation']??'',
                'token' => $data['token'],
            ];
            // Reset the password
            $status = Password::reset(
                $credentials,
                function ($user, $password) {
                    $user->forceFill([
                        'password' => bcrypt($password),
                    ])->save();
                }
            );

            // Handle the response
            if ($status === Password::PASSWORD_RESET) {
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
                errors: ['email' => __($status)]
            );
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
