<?php

namespace App\Http\Controllers;

use App\Enums\Http;
use App\Helpers\APIResponse;
use App\Http\Resources\TicketResource;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserTicketsResource;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * @OA\Tag(
 *     name="User",
 *     description="Endpoints for user profile and ticket management"
 * )
 */
class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/user/profile",
     *     tags={"User - Tickets"},
     *     summary="Get user profile with tickets",
     *     description="Retrieve the authenticated user's profile along with their associated tickets.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Data retrieved successfully"),
     *             @OA\Property(
     *                 property="body",
     *                 ref="#/components/schemas/UserTicketsResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Data not Found"),
     *             @OA\Property(property="errors", type="object", example={"user": "Data not Found"})
     *         )
     *     )
     * )
     */
    public function profile(UserRequest $request)
    {
        try {
            // Find the user by ID
            $userTickets = User::with('tickets')->where('id', Auth::id())->first();
            // If the ticket doesn't exist, return a 404 response
            if (!$userTickets)
                throw new NotFoundHttpException();

            // Return the ticket as a JSON response using UserTicketsResource
            return new APIResponse(
                status: 'success',
                code: Http::SUCCESS,
                message: __('validation.data_retrieved_successfully'),
                body: new UserTicketsResource($userTickets)
            );
        } catch (\Exception  $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user/tickets/{ticketId}",
     *     tags={"User - Tickets"},
     *     summary="Get a specific user ticket",
     *     description="Retrieve details of a specific ticket by its ID for the authenticated user.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="ticketId",
     *         in="path",
     *         description="ID of the ticket to retrieve",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Data retrieved successfully"),
     *             @OA\Property(
     *                 property="body",
     *                 ref="#/components/schemas/TicketResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Access denied",
     *         @OA\Response(ref="#/components/schemas/ForbiddenResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Data not found"),
     *             @OA\Property(property="errors", type="object", example={"ticket": "Data not found"})
     *         )
     *     )
     * )
     */
    public function showUserTicket($ticketId)
    {
        try {

            if (!Auth::user()->hasRole('user'))
                throw new AccessDeniedHttpException();
            // Find the ticket by ID
            $ticket = Ticket::where('ticket_id', $ticketId)->first();

            // If the ticket doesn't exist, return a 404 response
            if (!$ticket)
                throw new NotFoundHttpException();

            if ($ticket->user_id != Auth::id())
                throw new AccessDeniedHttpException();


            // Return the ticket as a JSON response using TicketResource
            return new APIResponse(
                status: 'success',
                code: Http::SUCCESS,
                message: __('validation.data_retrieved_successfully'),
                body: new TicketResource($ticket)
            );
        } catch (\Exception $e) {
            throw $e;
        }

    }

}
