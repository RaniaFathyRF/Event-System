<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Ticket;
use App\Helpers\UserTicketUtility;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class SyncTicketJob implements ShouldQueue
{
    use Queueable;

    public $titoTickets;

    /**
     * Create a new job instance.
     */
    public function __construct($tickets)
    {
        $this->titoTickets = $tickets;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel("syncTickets")->info('Starting tickets synchronization.', $this->titoTickets);

        try {
            foreach ($this->titoTickets as $titoTicket) {
                $ticketId = $titoTicket['reference'];
                $ticketName = "{$titoTicket['release_title']} ( #{$titoTicket['release_id']} )";
                $email = $titoTicket['email'];
                $name = $titoTicket['name'];
                $phone = $titoTicket['phone_number'] ?? '';
                $state = $titoTicket['state'];

                Log::channel("syncTickets")->info("Ticket ID" . $ticketId);

                $ticket = Ticket::where('ticket_id', $ticketId)->first();

                if (empty($ticket)) {
                    $this->handleNewTicket($name, $email, $phone, $ticketId, $ticketName, $state);
                } else {
                    $this->handleExistingTicket($ticket, $email, $name, $phone, $ticketName, $state);
                }
            }
        } catch (\Exception $exception) {
            Log::channel("syncTickets")->error('SyncTicketJob: ' . $exception->getMessage());
        } finally {
            Cache::forget('is_syncing');
        }
    }

    /**
     * @param $name
     * @param $email
     * @param $phone
     * @param $ticketId
     * @param $ticketName
     * @param $state
     * @return void
     */
    public function handleNewTicket($name, $email, $phone, $ticketId, $ticketName, $state)
    {
        Log::channel("syncTickets")->warning("Ticket not found.");
        $newUser = UserTicketUtility::createUser($name, $email, $phone, 'syncTickets');
        UserTicketUtility::createTicket($newUser, $ticketId, $ticketName, $state, 'syncTickets');
        Log::channel("syncTickets")->info("Ticket Created successfully: {$ticketId}");
    }

    /**
     * @param $ticket
     * @param $email
     * @param $name
     * @param $phone
     * @param $ticketName
     * @param $state
     * @return void
     */
    public function handleExistingTicket($ticket, $email, $name, $phone, $ticketName, $state)
    {
        if (empty($ticket->user_id)) {
            Log::channel("syncTickets")->warning("User not found.");
            return;
        }

        $ticketUser = User::find($ticket->user_id);

        if ($ticketUser->email !== $email) {
            $newUser = UserTicketUtility::createUser($name, $email, $phone, 'syncTickets');
            $ticket->user_id = $newUser->id;
        } else {
            UserTicketUtility::updateUser($ticketUser, ['name', 'phone'], [$name, $phone], 'syncTickets');
        }

        UserTicketUtility::updateTicket($ticket, ['ticket_name', 'status'], [$ticketName, $state], 'syncTickets');
        Log::channel("syncTickets")->info("Ticket updated successfully: " . $ticket->ticket_id);
    }


}
