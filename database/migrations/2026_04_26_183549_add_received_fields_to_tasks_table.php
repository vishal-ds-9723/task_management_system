<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('logo_received')->nullable()->after('type');
            $table->boolean('images_received')->nullable()->after('logo_received');
            $table->boolean('content_received')->nullable()->after('images_received');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'logo_received',
                'images_received',
                'content_received'
            ]);
        });
    }
};