<?php

namespace App\Helpers;

use App\Enums\Http;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\SendsResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Password;

class UserTicketUtility
{

    public function __construct()
    {

    }

    /**
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param string $logChannel
     * @return false
     */
    public static function createUser(string $name, string $email, string $phone, string $logChannel)
    {
        if (empty($name) || empty($email)) {
            Log::channel($logChannel)->warning("Name or email or phone is missing for creating user.");
            return false;
        }

        $user = User::where(['email' => $email])->first();
        if ($user)
            return $user;

        $user = User::Create(['email' => $email], [
            'name' => $name,
            'phone' => $phone,
            'password' => Hash::make(Str::random(10)),
        ])->assignRole('user');

        $status = Password::sendResetLink(['email' => $email]);
        Log::channel($logChannel)->info('Reset password email: ' . $status);
        return $user;
    }

    /**
     * @param User $newUser
     * @param string $ticketId
     * @param string $ticketName
     * @param string $state
     * @param string $logChannel
     * @return false
     */
    public static function createTicket(User $newUser, string $ticketId, string $ticketName, string $state, string $logChannel)
    {
        if (empty($newUser) || empty($ticketId) || empty($ticketName)) {
            Log::channel($logChannel)->warning(__('validation.ticket_creation_failed'));
            return false;
        }

        $new_ticket = Ticket::firstOrCreate(['ticket_id' => $ticketId], [
            'ticket_name' => $ticketName,
            'user_id' => $newUser->id,
            'status' => $state,
        ]);

        return $new_ticket;
    }

    /**
     * @param Ticket $ticket
     * @param array $columns
     * @param array $values
     * @param string $logChannel
     * @return Ticket
     */
    public static function updateTicket(Ticket $ticket, array $columns, array $values, string $logChannel)
    {
        if (empty($ticket) || empty($columns) || empty($values))
            Log::channel($logChannel)->warning(__('validation.missing_fields'));

        foreach ($columns as $key => $value) {
            $ticket->$value = $values[$key];
        }

        $ticket->save();
        Log::channel($logChannel)->info("Ticket updated successfully: ".$ticket->ticket_id);

        return $ticket;
    }

    /**
     * @param User $User
     * @param array $columns
     * @param array $values
     * @param string $logChannel
     * @return User
     */
    public static function updateUser(User $User, array $columns, array $values, string $logChannel)
    {
        if (empty($User) || empty($columns) || empty($values))
            Log::channel($logChannel)->warning(__('validation.missing_fields'));

        foreach ($columns as $key => $value) {
            $User->$value = $values[$key];
        }
        $User->save();

        return $User;

    }
}
