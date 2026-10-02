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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model'); // Allows media to be attached to any model
            $table->string('collection_name')->default('default'); // e.g., 'task-media', 'design-media'
            $table->string('name'); // Original filename
            $table->string('file_name'); // Stored filename
            $table->string('mime_type')->nullable();
            $table->string('disk')->default('public');
            $table->text('path');
            $table->bigInteger('size')->nullable();
            $table->string('type')->nullable(); // 'image', 'video', 'document'
            $table->text('metadata')->nullable(); // JSON - width, height, duration, etc.
            $table->integer('order_column')->nullable();
            $table->timestamps();
            
            $table->index(['model_id', 'model_type', 'collection_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
