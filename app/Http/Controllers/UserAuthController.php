<?php

namespace App\Http\Controllers;

use App\Helpers\APIResponse;
use App\Enums\Http;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserTokenResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\TitoService;
use App\Http\Requests\Auth\User\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


class UserAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/user/register",
     *     tags={"User"},
     *     summary="Register a new user",
     *     description="Register a new user with name, email, password, and optional phone number.",
     *     operationId="userRegister",
     *     @OA\Parameter(
     *            name="name",
     *            in="query",
     *            required=true,
     *            @OA\Schema(type="string", example="John Doe")
     *        ),
     *     @OA\Parameter(
     *           name="email",
     *           in="query",
     *           required=true,
     *           @OA\Schema(type="string", format="email", example="user@example.com")
     *       ),
     *       @OA\Parameter(
     *           name="password",
     *           in="query",
     *           required=true,
     *           @OA\Schema(type="string", format="password", example="password123")
     *       ),
     *       @OA\Parameter(
     *            name="password_confirmation",
     *            in="query",
     *            required=true,
     *            @OA\Schema(type="string", format="password", example="password123")
     *        ),
     *       @OA\Parameter(
     *             name="phone",
     *             in="query",
     *             required=true,
     *             @OA\Schema(type="string", example="01026354766")
     *         ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="body",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Invalid Tito ticket",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="No valid Tito ticket found for this email"),
     *             @OA\Property(property="errors", type="object", example={"tito_invalid_user": "No valid Tito ticket found for this email."})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request, TitoService $titoService)
    {
        try {
            $data = $request->validated();

            // Verify ticket via Tito API
            if (empty($this->hasValidTitoTicket($titoService, $data['email']))) {
                return new APIResponse(
                    status: 'fail',
                    code: Http::FORBIDDEN,
                    message: __('validation.tito_invalid_user'),
                    errors: ['tito_invalid_user' => __('validation.tito_invalid_user')]
                );
            }

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => isset($data['phone']) ? $data['phone'] : '',
            ])->assignRole('user');

            // Send verification email
            $user->sendEmailVerificationNotification();

            return new APIResponse(
                status: 'success',
                code: Http::SUCCESS,
                message: __('validation.register_successfully'),
                body: new UserResource($user)
            );
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param TitoService $titoService
     * @param $email
     * @return bool
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Validation\ValidationException
     */
    private function hasValidTitoTicket(TitoService $titoService, $email)
    {
        $response = $titoService->fetchAttendeeTickets($email);
        if (!empty($response['tickets']))
            return true;

        return false;
    }

    /**
     * @OA\Post(
     *     path="/api/user/login",
     *     tags={"User"},
     *     summary="Login and generate token",
     *     description="Authenticate user and generate an access token.",
     *     operationId="userLogin",
     *     @OA\Parameter(
     *           name="email",
     *           in="query",
     *           required=true,
     *           @OA\Schema(type="string", format="email", example="user@example.com")
     *       ),
     *       @OA\Parameter(
     *           name="password",
     *           in="query",
     *           required=true,
     *           @OA\Schema(type="string", format="password", example="password123")
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Logged in successful"),
     *             @OA\Property(
     *                 property="body",
     *                 ref="#/components/schemas/UserTokenResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Invalid credentials"),
     *             @OA\Property(property="errors", type="object", example={"password": {"The password is incorrect."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Email not verified",
     *         @OA\Response(ref="#/components/schemas/ForbiddenResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();

            $user = User::where('email', $data['email'])->first();


            if (!$user || !Hash::check($data['password'], $user->password)) {
                return new APIResponse(
                    status: "fail",
                    code: Http::UNAUTHENTICATED,
                    message: __('validation.invalid_credentials'),
                    errors: ["password" => [__("validation.password")]]
                );
            }
            if (!$user->hasRole('user'))
                throw new AuthorizationException();

            if (!$user->hasVerifiedEmail()) {
                return new APIResponse(
                    status: "fail",
                    code: Http::FORBIDDEN,
                    message: __('validation.email_not_verified'),
                    errors: ["email_not_verified" => [__("validation.email_not_verified")]]
                );
            }

            // Delete all existing tokens for the user
            $user->tokens()->delete();

            return new APIResponse(
                status: "success",
                code: Http::SUCCESS,
                message: __('validation.login_successfully'),
                body: new UserTokenResource($user)
            );

        } catch (\Exception $exception) {
            throw $exception;
        }


    }

    /**
     * @OA\Post(
     *     path="/api/user/logout",
     *     tags={"User"},
     *     summary="Logout user",
     *     description="Revoke all tokens for the authenticated user.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Logged out successfully"),
     *             @OA\Property(property="errors", type="object", example={"logged out": "Logged out successfully"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Access denied",
     *         @OA\Response(ref="#/components/schemas/ForbiddenResponse")
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {

            if (!$request->user()->hasRole('user'))
                throw new AccessDeniedHttpException();

            // Revoke all tokens for the authenticated user
            $request->user()->tokens()->delete();
            return new APIResponse(
                status: 'success',
                code: Http::SUCCESS,
                message: __('validation.logout_successfully'),
                body: [__('validation.logout_successfully')]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
