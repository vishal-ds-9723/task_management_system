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
        Schema::create('client_monthly_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedSmallInteger('year'); // 2026, etc
            $table->unsignedInteger('posts')->default(0);
            $table->unsignedInteger('reels')->default(0);
            $table->unsignedInteger('stories')->default(0);
            $table->unsignedInteger('carousel')->default(0);
            $table->unsignedInteger('videos')->default(0);
            $table->unsignedInteger('guides')->default(0);
            $table->unsignedInteger('collections')->default(0);
            $table->unsignedInteger('other')->default(0);
            $table->timestamps();
            $table->unique(['client_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_monthly_schedules');
    }
};
