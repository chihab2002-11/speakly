<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->date('paid_on');
            $table->string('method', 30)->default('cash');
            $table->string('reference', 120)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index(['student_id', 'paid_on']);
            $table->index(['parent_id', 'paid_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuition_payments');
    }
};
