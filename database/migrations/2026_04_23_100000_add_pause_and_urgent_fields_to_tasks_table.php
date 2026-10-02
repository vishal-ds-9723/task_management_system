<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_paused')->default(false)->after('status');
            $table->timestamp('paused_at')->nullable()->after('is_paused');
            $table->string('pause_reason')->nullable()->after('paused_at');
            $table->unsignedInteger('total_paused_seconds')->default(0)->after('pause_reason');
            $table->boolean('is_urgent_task')->default(false)->after('total_paused_seconds');
            $table->string('urgent_requested_by')->nullable()->after('is_urgent_task');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'is_paused', 'paused_at', 'pause_reason',
                'total_paused_seconds', 'is_urgent_task', 'urgent_requested_by',
            ]);
        });
    }
};
