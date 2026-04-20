<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasColumn('courses', 'price')) {
            return;
        }

        DB::table('courses')
            ->whereNull('price')
            ->orWhere('price', '<=', 0)
            ->update(['price' => 0]);

        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(0)->change();
        });
    }

    public function down(): void
    {
        //
    }
};
