<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('festivals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->string('emoji', 10)->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // religious, national, international, awareness, cultural
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('festival_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['festival_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('festival_selections');
        Schema::dropIfExists('festivals');
    }
};
