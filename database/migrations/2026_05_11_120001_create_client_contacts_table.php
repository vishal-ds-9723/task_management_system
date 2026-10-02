<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('role')->nullable(); // e.g. "Marketing Director", "Owner"
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0); // for ordering
            $table->timestamps();

            $table->index(['client_id', 'is_primary']);
            $table->index(['client_id', 'position']);
        });

        // Backfill: every existing client with any contact info becomes a primary contact
        $now = now();
        DB::table('clients')->orderBy('id')->chunkById(500, function ($clients) use ($now) {
            $rows = [];
            foreach ($clients as $c) {
                if (! empty($c->contact_person) || ! empty($c->contact_email) || ! empty($c->contact_phone)) {
                    $rows[] = [
                        'client_id'  => $c->id,
                        'name'       => $c->contact_person ?: null,
                        'email'      => $c->contact_email  ?: null,
                        'phone'      => $c->contact_phone  ?: null,
                        'role'       => null,
                        'is_primary' => true,
                        'position'   => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if (! empty($rows)) {
                DB::table('client_contacts')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contacts');
    }
};
