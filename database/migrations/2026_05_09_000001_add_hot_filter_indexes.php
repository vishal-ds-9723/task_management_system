<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_idx');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->index('is_active', 'clients_is_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_idx');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_is_active_idx');
        });
    }
};
