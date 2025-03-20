<?php

namespace App\Http\Controllers;


/**
 * @OA\Info(
 *     title="Ticket Management API",
 *     version="1.0.0",
 *     description="API for admin authentication"
 * ),
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 *  ),
 * @OA\Schema(
 *       schema="TicketCollection",
 *       type="object",
 *       @OA\Property(
 *           property="data",
 *           type="array",
 *           @OA\Items(ref="#/components/schemas/TicketResource")
 *       ),
 *       @OA\Property(
 *           property="pagination",
 *           type="object",
 *           @OA\Property(property="current_page", type="integer"),
 *           @OA\Property(property="last_page", type="integer"),
 *           @OA\Property(property="per_page", type="integer"),
 *           @OA\Property(property="total", type="integer")
 *       )
 *  )
 * @OA\Schema(
 *       schema="TicketResource",
 *       type="object",
 *       @OA\Property(property="ticket_id", type="string",example="SQV2"),
 *       @OA\Property(property="ticket_name", type="string",example="SQV2 (#123765)"),
 *       @OA\Property(property="status", type="string", enum={"void", "complete", "incomplete"},example="complete"),
 *       @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
 *       @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
 *       @OA\Property(
 *           property="user",
 *           ref="#/components/schemas/User"
 *       )
 *  ),
 * @OA\Schema(
 *      schema="UserTicketsResource",
 *      type="object",
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="name", type="string", example="John Doe"),
 *      @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *      @OA\Property(property="phone", type="string", example="01026354766"),
 *      @OA\Property(property="email_verified_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
 *      @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
 *      @OA\Property(
 *          property="tickets",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/TicketResource")
 *      )
 *  ),
 * @OA\Schema(
 *      schema="UserTokenResource",
 *      type="object",
 *      @OA\Property(
 *           property="user",
 *           ref="#/components/schemas/User"
 *       ),
 *      @OA\Property(
 *          property="token",
 *          type="string",
 *          example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
 *      ),
 *      @OA\Property(
 *           property="token_type",
 *           type="string",
 *           example="Bearer "
 *       ),
 *      @OA\Property(
 *           property="expires_in",
 *           type="int",
 *           example=1234567
 *       ),
 *  )
 * @OA\Schema(
 *       schema="ForbiddenResponse",
 *       type="object",
 *       @OA\Property(property="status", type="string", example="fail"),
 *       @OA\Property(property="code", type="integer", example=403),
 *       @OA\Property(property="message", type="string", example="User is not authorized."),
 *       @OA\Property(property="errors", type="object", example={"unauthorized":"User is not authorized."})
 *   )
 * @OA\Schema(
 *       schema="User",
 *       type="object",
 *       @OA\Property(property="id", type="integer", example=1),
 *       @OA\Property(property="name", type="string", example="John Doe"),
 *       @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *       @OA\Property(property="phone", type="string", example="01026354766"),
 *       @OA\Property(property="email_verified_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
 *       @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
 *       @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z")
 *  )
 */
abstract class Controller
{
    //
}
