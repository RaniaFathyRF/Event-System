<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\UserTicketUtility;


class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticketId;
    public $ticketName;
    public $state;

    public $email;
    public $name;
    public $phone;
    public $ticket;
    public $trigger;

    /**
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->ticket = $payload['reference'];
        $this->ticketId = $payload['reference'];
        $this->ticketName = $payload['release_title'] . ' ( #' . $payload['release_id'] . ' )';
        $this->state = $payload['state_name'];
        $this->email = $payload['email'];
        $this->name = $payload['name'];
        $this->phone = $payload['phone_number'];
        $this->trigger = $payload['trigger'];

    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::channel('webhook')->info("Start Processing ticket data from queue", [$this->ticketId, $this->ticketName, $this->email, $this->name, $this->phone, $this->state, $this->trigger]);
        // get Ticket by ticket id
        $ticket = Ticket::withTrashed()->where('ticket_id', $this->ticketId)->first();

        return $this->handleTicketUpdateOrCreation($ticket, $this->ticketId, $this->ticketName, $this->state, $this->name, $this->email, $this->phone, $this->trigger);

    }

    /**
     * @param Ticket|null $ticket
     * @param string $ticketId
     * @param string $ticketName
     * @param string $state
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param string $trigger
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleTicketUpdateOrCreation(?Ticket $ticket, string $ticketId, string $ticketName, string $state, string $name, string $email, string $phone, string $trigger)
    {

        if (!$ticket) {
            Log::channel('webhook')->warning("Ticket not found.");
            return $this->createUserAndTicket($name, $email, $phone, $ticketId, $ticketName, $state, $trigger);
        }

        return $this->updateORCreateUserAndTicket($ticket, $ticketId, $ticketName, $state, $name, $email, $phone, $trigger);
    }

    /**
     * @param Ticket $ticket
     * @param string $ticketId
     * @param string $ticketName
     * @param string $state
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @param string|null $trigger
     * @return \Illuminate\Http\JsonResponse
     */
    private function updateORCreateUserAndTicket(Ticket $ticket, string $ticketId, string $ticketName, string $state, string $name, string $email, ?string $phone, ?string $trigger)
    {
        if (empty($ticket) || empty($ticketId) || empty($ticketName) || empty($state) || empty($name)) {
            Log::channel('webhook')->warning(__('validation.missing_fields'));
            return response()->json(['error' => __('validation.missing_fields')], 400);
        }
        // get user ticket data
        $ticketUser = User::where('id', $ticket->user_id)->first();
        // if email changed
        if ($ticketUser->email != $email) {
            // create new user
            $newUser = UserTicketUtility::createUser($name, $email, $phone,'webhook');
            // update ticket user_id
            $ticket->user_id = $newUser->id;
        } else {
            // update user
            UserTicketUtility::updateUser($ticketUser, ['name', 'phone'], [$name, $phone],'webhook');
        }
        // update ticket Details
        UserTicketUtility::updateTicket($ticket, ['ticket_name', 'status'], [$ticketName, $state], 'webhook');

        if (!empty($trigger) && $trigger == 'ticket.voided') {
            $ticket->delete();
            Log::channel('webhook')->info("Ticket {$ticketId}  soft-deleted (voided).");
        } else if (!empty($trigger) && $trigger == 'ticket.unvoided' && $ticket->trashed()) {
            $ticket->restore();
            Log::channel('webhook')->info("Ticket {$ticketId} restored (unvoided).");

        }
        Log::channel('webhook')->info("Webhook processed successfully." . $ticketId);
        return response()->json(['message' => 'Webhook processed successfully.'], 200);
    }

    /**
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @param string $ticketId
     * @param string $ticketName
     * @param string $state
     * @param string $trigger
     * @return \Illuminate\Http\JsonResponse
     */
    private function createUserAndTicket(string $name, string $email, ?string $phone, string $ticketId, string $ticketName, string $state, string $trigger)
    {
        // create new user
        $newUser = UserTicketUtility::createUser($name, $email, $phone,'webhook');
        // create new Ticket
        $newTicket = UserTicketUtility::createTicket($newUser, $ticketId, $ticketName, $state,'webhook');
        // soft delete ticket if it void
        if (!empty($trigger) && !empty($newTicket) && $trigger == 'ticket.voided') {
            $newTicket->delete();
            Log::channel('webhook')->info("Ticket {$ticketId}  soft-deleted (voided).");
        }

        Log::channel('webhook')->info("Webhook processed successfully." . $ticketId);
        return response()->json(['message' => 'Webhook processed successfully.'], 200);
    }

}




