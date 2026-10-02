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
            $table->date('project_start_date')->nullable()->after('content_received');
            $table->date('launch_date')->nullable()->after('project_start_date');
            $table->date('dev_deadline')->nullable()->after('launch_date');
            $table->string('preferred_tech')->nullable()->after('dev_deadline');
            $table->text('modules')->nullable()->after('preferred_tech');
            $table->text('features')->nullable()->after('modules');
            $table->boolean('domain_purchased')->nullable()->after('features');
            $table->boolean('hosting_access')->nullable()->after('domain_purchased');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'project_start_date',
                'launch_date',
                'dev_deadline',
                'preferred_tech',
                'modules',
                'features',
                'domain_purchased',
                'hosting_access',
            ]);
        });
    }
};
