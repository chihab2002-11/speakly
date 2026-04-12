<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_cards', function (Blueprint $table) {
            $table->dropColumn('blood_type');
        });

        $cards = DB::table('student_cards')->select('id', 'user_id')->get();

        foreach ($cards as $card) {
            $approvedAt = DB::table('users')->where('id', $card->user_id)->value('approved_at');

            if (! $approvedAt) {
                continue;
            }

            $approved = Carbon::parse($approvedAt);
            $registrationYear = $approved->year;

            DB::table('student_cards')
                ->where('id', $card->id)
                ->update([
                    'valid_from' => $approved->toDateString(),
                    'valid_to' => $approved->copy()->addMonths(6)->toDateString(),
                    'academic_year' => $registrationYear.'/'.($registrationYear + 1),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_cards', function (Blueprint $table) {
            $table->string('blood_type', 5)->nullable();
        });
    }
};
