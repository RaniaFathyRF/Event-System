<?php

use App\Http\Controllers\Auth\User\NewPasswordController;
use App\Http\Controllers\Auth\User\PasswordResetLinkController;
use App\Http\Controllers\Auth\User\EmailVerificationController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserAuthController;

use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\TitoWebhookController;

/**
 * Admin Authentication
 */

Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware(['timeout', 'throttle:6,1']);

Route::middleware(['auth:sanctum', 'timeout', 'throttle:6,1'])->group(function () {
    Route::get('/admin/tickets', [TicketController::class, 'listData']);
    Route::get('/admin/tickets/{ticketId}', [TicketController::class, 'showTicket']);
    Route::delete('/admin/tickets/{ticketId}', [TicketController::class, 'deleteTicket']);
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
});

/**
 * User Authentication
 */
Route::middleware(['timeout', 'throttle:6,1'])->group(function () {

    Route::post('/user/register', [UserAuthController::class, 'register']);
    Route::post('/user/login', [UserAuthController::class, 'login']);

    Route::post('/user/forgot-password', [PasswordResetLinkController::class, 'send']);

    Route::post('/user/reset-password', [NewPasswordController::class, 'reset']);
    // Email Verification
    Route::get('/user/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');
    Route::post('/user/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->name('verification.send');

});


/**
 * User Profile Routes
 */
Route::middleware(['auth:sanctum', 'timeout', 'throttle:6,1'])->group(function () {

    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::get('/user/tickets/{ticketId}', [UserController::class, 'showUserTicket']);
    Route::post('/user/logout', [UserAuthController::class, 'logout']);
});

//Tito Webhook Route
Route::post('/tito-webhook', [TitoWebhookController::class, 'handleWebhook']);

