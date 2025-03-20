<?php

namespace App\Http\Controllers;

use App\Enums\Http;
use App\Helpers\APIResponse;
use App\Http\Resources\UserTokenResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Endpoints for admin authentication & ticket management"
 * ),
 *
 */
class AdminAuthController extends Controller
{
    /**
     * Admin Login
     *
     * @OA\Post(
     *     path="/api/admin/login",
     *     tags={"Admin"},
     *     summary="Admin login to generate a token",
     *     operationId="adminLogin",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(type="string", format="email", example="admin@example.com")
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(type="string", format="password", example="password123")
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Logged in Successfully"),
     *             @OA\Property(
     *                 property="body",
     *                 ref="#/components/schemas/UserTokenResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Invalid credentials"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="password",
     *                     type="array",
     *                     @OA\Items(type="string", example="The password is incorrect.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\Response(ref="#/components/schemas/ForbiddenResponse")
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

            if (!$user->hasRole('admin'))
                throw new AccessDeniedHttpException();

            // Delete all existing tokens for the user
            $user->tokens()->delete();

            return new APIResponse(
                status: "success",
                code: Http::SUCCESS,
                message: __('validation.login_successfully'),
                body: new UserTokenResource($user)
            );

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Admin Logout
     *
     * @OA\Post(
     *     path="/api/admin/logout",
     *     tags={"Admin"},
     *     summary="Admin logout to revoke token",
     *     operationId="adminLogout",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Logged out successfully"),
     *             @OA\Property(property="body", type="object", example={"Logged out successfully"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\Response(ref="#/components/schemas/ForbiddenResponse")
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            if (!$request->user()->hasRole('admin'))
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
