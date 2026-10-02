<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('festival_selections', function (Blueprint $table) {
            $table->json('platforms')->nullable()->after('user_id'); // ["instagram","facebook"]
        });
    }

    public function down(): void
    {
        Schema::table('festival_selections', function (Blueprint $table) {
            $table->dropColumn('platforms');
        });
    }
};
