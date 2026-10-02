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
            $table->foreignId('admin_approved_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approved_by');
            $table->string('admin_approval_status', 20)->nullable()->after('admin_approved_at'); // approved, rejected
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['admin_approved_by']);
            $table->dropColumn(['admin_approved_by', 'admin_approved_at', 'admin_approval_status']);
        });
    }
};
