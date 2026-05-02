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
        Schema::table('language_programs', function (Blueprint $table) {
            $table->string('flag_url', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('language_programs', function (Blueprint $table) {
            $table->string('flag_url', 500)->nullable(false)->change();
        });
    }
};
