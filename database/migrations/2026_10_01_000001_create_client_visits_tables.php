<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_visits')) {
            Schema::create('client_visits', function (Blueprint $table) {
                $table->id();
                $table->enum('visit_type', ['lead', 'client'])->default('client');
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->string('lead_name')->nullable();
                $table->string('company_name')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->string('location')->nullable();
                $table->string('meeting_mode')->default('in_person'); // in_person, client_office, agency_office, virtual_call, other
                $table->foreignId('visited_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('visit_date');
                $table->string('purpose')->nullable(); // Discovery, Pitch, Review, Shoot, Relationship, etc.
                $table->string('status')->default('scheduled'); // scheduled, completed, follow_up_needed, converted, cancelled
                $table->text('summary')->nullable(); // Meeting summary / key notes
                $table->text('discussion_points')->nullable();
                $table->text('action_items')->nullable();
                $table->date('next_follow_up')->nullable();
                $table->string('attachment')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['visit_type', 'status']);
                $table->index('visit_date');
                $table->index('next_follow_up');
            });
        }

        if (!Schema::hasTable('client_visit_updates')) {
            Schema::create('client_visit_updates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_visit_id')->constrained('client_visits')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('update_type')->default('note'); // note, call, meeting, proposal, status_change, follow_up
                $table->text('note');
                $table->string('status_from')->nullable();
                $table->string('status_to')->nullable();
                $table->date('next_follow_up')->nullable();
                $table->string('attachment')->nullable();
                $table->timestamps();

                $table->index('client_visit_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_visit_updates');
        Schema::dropIfExists('client_visits');
    }
};
