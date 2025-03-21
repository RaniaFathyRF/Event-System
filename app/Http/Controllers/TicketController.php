<?php


namespace App\Http\Controllers;

use App\Helpers\APIResponse;
use App\Enums\Http;
use App\Http\Resources\TicketCollection;
use App\Http\Resources\TicketResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\TicketRequest;
use App\Models\Ticket;
use App\Services\TitoService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class TicketController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/tickets",
     *     tags={"Admin"},
     *     summary="List all tickets",
     *     description="Retrieve a list of tickets with optional filtering, sorting, and pagination.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *     ),
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         description="JSON string for filtering tickets",
     *         required=false,
     *         @OA\Schema(type="string", example={"ticket_id":"NQSR1","ticket_name":"NQSR1 (#123456)","status":"complete","user_name":"admin","user_email":"admin@example.com","user_phone":"01020384566"})
     *     ),
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="A list of tickets",
     *         @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="Data retrieved successfully"),
     *              @OA\Property(
     *                  property="body",
     *                  ref="#/components/schemas/TicketCollection"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\Response(ref="#/components/schemas/ForbiddenResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No tickets found",
     *         @OA\JsonContent(
     *               @OA\Property(property="status", type="string", example="fail"),
     *               @OA\Property(property="code", type="integer", example=404),
     *               @OA\Property(property="message", type="string", example="NO Results Found."),
     *               @OA\Property(property="errors", type="object", example={"data":"NO Results Found."}),
     *          )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *               @OA\Property(property="status", type="string", example="fail"),
     *               @OA\Property(property="code", type="integer", example=422),
     *               @OA\Property(property="message", type="string", example="Validation failed."),
     *               @OA\Property(property="errors", type="object", example={"orderby":"Orderby is not valid"})
     *           )
     *     )
     * )
     */
    public function listTickets(TicketRequest $request)
    {
        try {
            if (!Auth::user()->hasRole('admin')) {
                throw new AccessDeniedHttpException();
            }

            // Validate query parameters
            $data = $request->validated();

            // Set default values
            $page = $data['page'] ?? 1;
            $limit = $data['limit'] ?? 10;
            $sort = $data['sort'] ?? 'created_at';
            $order = $data['order'] ?? 'asc';
            $filter = json_decode($data['filter'] ?? '{}', true);

            // Validate fields inside the filter JSON
            $validator = Validator::make($filter, [
                'ticket_id' => 'sometimes|string|max:255',
                'ticket_name' => 'sometimes|string|max:255',
                'status' => 'sometimes|string|in:void,complete,incomplete',
                'user_name' => 'sometimes|string|max:255',
                'user_email' => 'sometimes|email|max:255',
                'user_phone' => 'sometimes|string|max:255',
            ]);

            // If validation fails, return error response
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Query with filtering
            $query = Ticket::query();

            // get query filters
            $query = $this->filterTickets($query, $filter);

            // Query with sorting
            $query->orderBy($sort, $order);

            // Paginate the results
            $results = $query->paginate($limit, ['*'], 'page', $page);

            // Check if results are empty
            if ($results->isEmpty()) {
                throw new NotFoundHttpException();
            }

            // Return the response
            return new APIResponse(
                status: 'success',
                code: Http::SUCCESS,
                message: __('validation.data_retrieved_successfully'),
                body: new TicketCollection($results->items(), [
                    'pagination' => [
                        'current_page' => $results->currentPage(),
                        'last_page' => $results->lastPage(),
                        'per_page' => $results->perPage(),
                        'total' => $results->total(),
                    ],
                ])
            );

        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * @OA\Get(
     *     path="/api/admin/tickets/{ticketId}",
     *     tags={"Admin"},
     *     summary="Get a specific ticket",
     *     description="Retrieve details of a specific ticket by its ID.",
     *     @OA\Parameter(
     *         name="ticketId",
     *         in="path",
     *         description="ID of the ticket to retrieve",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Ticket details",
     *         @OA\JsonContent(
     *               @OA\Property(property="status", type="string", example="success"),
     *               @OA\Property(property="code", type="integer", example=200),
     *               @OA\Property(property="message", type="string", example="Data retrieved successfully"),
     *               @OA\Property(
     *                   property="body",
     *                   ref="#/components/schemas/TicketResource"
     *               )
     *           )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\Response(ref="#/components/schemas/ForbiddenResponse")
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Ticket not found",
     *          @OA\JsonContent(
     *                @OA\Property(property="status", type="string", example="fail"),
     *                @OA\Property(property="code", type="integer", example=404),
     *                @OA\Property(property="message", type="string", example="NO Results Found."),
     *                @OA\Property(property="errors", type="object", example={"data":"NO Results Found."})
     *            )
     *     ),
     * )
     */
    public function showTicket($ticketId, TitoService $titoService)
    {
        try {

            if (!Auth::user()->hasRole('admin'))
                throw new AccessDeniedHttpException();

            // Find the ticket by ID
            $ticket = Ticket::where('ticket_id', $ticketId)->first();
            // If the ticket doesn't exist, return a 404 response
            if (!$ticket)
                throw new NotFoundHttpException();


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
    /**
     * @OA\Delete(
     *     path="/api/admin/tickets/{ticketId}",
     *     tags={"Admin"},
     *     summary="Delete a specific ticket",
     *     description="Delete a specific ticket by its ID.",
     *     @OA\Parameter(
     *         name="ticketId",
     *         in="path",
     *         description="ID of the ticket to delete",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Ticket deleted successfully",
     *        @OA\JsonContent(
     *                @OA\Property(property="status", type="string", example="success"),
     *                @OA\Property(property="code", type="integer", example=200),
     *                @OA\Property(property="message", type="string", example="Ticket deleted successfully"),
     *                @OA\Property(
     *                    property="body",
     *                    ref="#/components/schemas/TicketResource"
     *                )
     *            )
     *       ),
     *     ),
     *     @OA\Response(
     *            response=403,
     *            description="Forbidden",
     *            @OA\Response(ref="#/components/schemas/ForbiddenResponse")
     *     ),
     *     @OA\Response(
     *           response=404,
     *           description="Ticket not found",
     *           @OA\JsonContent(
     *                 @OA\Property(property="status", type="string", example="fail"),
     *                 @OA\Property(property="code", type="integer", example=404),
     *                 @OA\Property(property="message", type="string", example="NO Results Found."),
     *                 @OA\Property(property="errors", type="object", example={"data":"NO Results Found."})
     *             )
     *     ),
     * )
     */
    public function deleteTicket($ticketId)
    {
        try {

            if (!Auth::user()->hasRole('admin'))
                throw new AccessDeniedHttpException();

            // Find the ticket by ID
            $ticket = Ticket::where('ticket_id', $ticketId)->first();

            // If the ticket doesn't exist, return a 404 response
            if (!$ticket)
                throw new NotFoundHttpException();
            // delete ticket
            $ticket->delete();
            // Return the ticket as a JSON response using TicketResource
            return new APIResponse(
                status: 'success',
                code: Http::SUCCESS,
                message: __('validation.data_deleted_successfully'),
                body: new TicketResource($ticket)
            );

        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * @param $query
     * @param array|null $filter
     * @return mixed
     */
    public function filterTickets($query, ?array $filter)
    {
        if (empty($filter))
            return $query;

        foreach ($filter as $field => $value) {
            switch ($field) {
                case 'user_name':
                    $query->whereHas('user', function ($q) use ($value) {
                        $q->where('name', 'like', "%$value%");
                    });
                    break;
                case 'user_email':
                    $query->whereHas('user', function ($q) use ($value) {
                        $q->where('email', 'like', "%$value%");
                    });
                    break;
                case 'user_phone':
                    $query->whereHas('user', function ($q) use ($value) {
                        $q->where('phone', 'like', "%$value%");
                    });
                    break;
                case 'ticket_id':
                case 'ticket_name':
                case 'status':
                    $query->where($field, 'like', "%$value%");
                    break;
                default:
                    throw new NotFoundHttpException();

            }

        }
        return $query;
    }
}


