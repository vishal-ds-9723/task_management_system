<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_user')) {
            Schema::create('client_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'client_id']);
            });

            // Backfill existing user.client_id assignments into client_user
            $existingAssignments = DB::table('users')
                ->whereNotNull('client_id')
                ->select('id as user_id', 'client_id', 'created_at', 'updated_at')
                ->get();

            foreach ($existingAssignments as $row) {
                DB::table('client_user')->insertOrIgnore([
                    'user_id' => $row->user_id,
                    'client_id' => $row->client_id,
                    'created_at' => $row->created_at ?: now(),
                    'updated_at' => $row->updated_at ?: now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_user');
    }
};
