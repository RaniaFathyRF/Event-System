<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Linked to users table
            $table->string('ticket_id'); // Ticket ID from Tito API
            $table->string('ticket_name'); // Event name
            $table->string('status'); // Ticket status (e.g., "confirmed", "cancelled")
            $table->timestamps();
            $table->softDeletes(); // Allows soft deletion for admin
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
