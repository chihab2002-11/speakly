<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('offer_key', 80);
            $table->unsignedTinyInteger('discount_percent');
            $table->timestamp('activated_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['parent_id', 'offer_key', 'student_id'], 'scholarship_activation_unique');
            $table->index(['parent_id', 'activated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_activations');
    }
};
