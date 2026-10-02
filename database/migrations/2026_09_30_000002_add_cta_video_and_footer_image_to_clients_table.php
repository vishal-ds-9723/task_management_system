<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'cta_video')) {
                $table->string('cta_video')->nullable()->after('logo');
            }
            if (!Schema::hasColumn('clients', 'footer_image')) {
                $table->string('footer_image')->nullable()->after('cta_video');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'footer_image')) {
                $table->dropColumn('footer_image');
            }
            if (Schema::hasColumn('clients', 'cta_video')) {
                $table->dropColumn('cta_video');
            }
        });
    }
};
