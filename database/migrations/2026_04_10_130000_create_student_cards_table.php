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
        Schema::create('student_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('card_number')->unique();
            $table->string('blood_type', 5)->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->string('academic_year', 9);
            $table->enum('status', ['active', 'inactive', 'expired', 'suspended'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['valid_from', 'valid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_cards');
    }
};
