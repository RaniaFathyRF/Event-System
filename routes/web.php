<?php

use App\Helpers\UserTicketUtility;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Services\TitoService;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});





