<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanTimetableFinancialSeeder extends Seeder
{
    public function run(): void
    {
        $tablesToClean = [
            'attendance_records',
            'teacher_resources',
            'class_student',
            'schedules',
            'tuition_payments',
            'classes',
            'courses',
            'rooms',
        ];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tablesToClean as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->call([
            TimetableFinancialVisualizationSeeder::class,
        ]);

        if ($this->command !== null) {
            $this->command->info('Timetable/financial tables cleaned and reseeded.');
            $this->command->line('Users and roles were not deleted.');
        }
    }
}
