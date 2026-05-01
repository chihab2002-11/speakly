<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_document_type')
                ->nullable()
                ->after('requested_course_id');
            $table->string('registration_document_original_filename')
                ->nullable()
                ->after('registration_document_type');
            $table->string('registration_document_path')
                ->nullable()
                ->after('registration_document_original_filename');
            $table->string('registration_document_mime_type')
                ->nullable()
                ->after('registration_document_path');
            $table->unsignedBigInteger('registration_document_size')
                ->nullable()
                ->after('registration_document_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'registration_document_type',
                'registration_document_original_filename',
                'registration_document_path',
                'registration_document_mime_type',
                'registration_document_size',
            ]);
        });
    }
};
