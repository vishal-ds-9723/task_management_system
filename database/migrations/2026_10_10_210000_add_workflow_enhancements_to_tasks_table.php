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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'headline_copy')) {
                $table->text('headline_copy')->nullable()->after('brief');
            }
            if (!Schema::hasColumn('tasks', 'revision_target')) {
                $table->string('revision_target', 30)->nullable()->default('designer')->after('revision_count');
            }
            if (!Schema::hasColumn('tasks', 'requires_client_approval')) {
                $table->boolean('requires_client_approval')->default(false)->after('admin_approval_status');
            }
            if (!Schema::hasColumn('tasks', 'client_approved_at')) {
                $table->timestamp('client_approved_at')->nullable()->after('requires_client_approval');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'headline_copy',
                'revision_target',
                'requires_client_approval',
                'client_approved_at',
            ]);
        });
    }
};
