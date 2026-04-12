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
        Schema::create('language_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('locale_code', 12)->nullable();
            $table->string('name', 120);
            $table->string('title', 160);
            $table->string('description', 300);
            $table->text('full_description');
            $table->string('flag_url', 500);
            $table->json('certifications')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_programs');
    }
};
