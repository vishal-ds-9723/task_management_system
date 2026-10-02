<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'tech_stack')) {
                $table->string('tech_stack', 50)->nullable()->after('content_received');
            }
            if (!Schema::hasColumn('tasks', 'project_notes')) {
                $table->text('project_notes')->nullable()->after('tech_stack');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(array_filter(['tech_stack', 'project_notes'], fn ($col) => Schema::hasColumn('tasks', $col)));
        });
    }
};
