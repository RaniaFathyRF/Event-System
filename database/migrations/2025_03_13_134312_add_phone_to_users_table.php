<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneToUsersTable extends Migration {
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            // Add the 'phone' column
            $table->string('phone')->nullable()->after('email');
        });
    }

    public function down() {
        Schema::table('users', function (Blueprint $table) {
            // Drop the 'phone' column if the migration is rolled back
            $table->dropColumn('phone');
        });
    }
};
