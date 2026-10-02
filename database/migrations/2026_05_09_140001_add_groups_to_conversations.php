<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add group-related columns to conversations
        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('is_group')->default(false)->after('id');
            $table->string('name')->nullable()->after('is_group');
            $table->foreignId('created_by')->nullable()->after('name')->constrained('users')->nullOnDelete();
            // Allow nullable for groups (groups don't use the canonical pair columns)
            $table->unsignedBigInteger('user_one_id')->nullable()->change();
            $table->unsignedBigInteger('user_two_id')->nullable()->change();
        });

        // 2. Create the participants pivot — the universal source of truth for membership
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
            $table->index(['user_id', 'left_at']);
        });

        // 3. Backfill: for every existing conversation, populate the pivot from user_one_id / user_two_id
        $now = now();
        DB::table('conversations')->orderBy('id')->chunkById(500, function ($rows) use ($now) {
            $inserts = [];
            foreach ($rows as $c) {
                if ($c->user_one_id) {
                    $inserts[] = ['conversation_id' => $c->id, 'user_id' => $c->user_one_id, 'joined_at' => $now, 'created_at' => $now, 'updated_at' => $now];
                }
                if ($c->user_two_id) {
                    $inserts[] = ['conversation_id' => $c->id, 'user_id' => $c->user_two_id, 'joined_at' => $now, 'created_at' => $now, 'updated_at' => $now];
                }
            }
            if (! empty($inserts)) {
                DB::table('conversation_participants')->insertOrIgnore($inserts);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['is_group', 'name', 'created_by']);
        });
    }
};
