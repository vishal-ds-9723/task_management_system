<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tasks')
            ->select('id', 'caption')
            ->where('is_urgent_task', true)
            ->whereNull('brief')
            ->whereNotNull('caption')
            ->where(function ($query) {
                $query->whereNull('hashtags')
                    ->orWhere('hashtags', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($tasks): void {
                foreach ($tasks as $task) {
                    DB::table('tasks')
                        ->where('id', $task->id)
                        ->update([
                            'brief' => $task->caption,
                            'caption' => null,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Irreversible data backfill.
    }
};
