<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\StudentTuition;
use App\Models\TuitionPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ParentFinancialScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ParentDashboardLinkedChildrenSeeder::class,
        ]);

        $parent = User::query()->where('email', 'salahkedja1parent@gmail.com')->first();

        if (! $parent) {
            return;
        }

        $thirdChild = User::query()->firstOrCreate(
            ['email' => 'salahkedja1.child.c@gmail.com'],
            [
                'name' => 'Nour Kedja',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $thirdChild->forceFill([
            'name' => 'Nour Kedja',
            'parent_id' => $parent->id,
            'requested_role' => 'student',
            'approved_at' => $thirdChild->approved_at ?? now(),
            'approved_by' => $thirdChild->approved_by,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();
        $thirdChild->syncRoles(['student']);

        $children = User::query()
            ->where('parent_id', $parent->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'student'))
            ->orderBy('id')
            ->get();

        if ($children->isEmpty()) {
            return;
        }

        $courses = [
            Course::query()->firstOrCreate(['code' => 'FIN-ENG-B2'], ['name' => 'English B2', 'price' => 38000]),
            Course::query()->firstOrCreate(['code' => 'FIN-FRE-B1'], ['name' => 'French B1', 'price' => 34000]),
            Course::query()->firstOrCreate(['code' => 'FIN-SPA-A2'], ['name' => 'Spanish A2', 'price' => 32000]),
            Course::query()->firstOrCreate(['code' => 'FIN-ITA-A1'], ['name' => 'Italian A1', 'price' => 30000]),
            Course::query()->firstOrCreate(['code' => 'FIN-GER-A1'], ['name' => 'German A1', 'price' => 30000]),
        ];

        $teacher = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))->first();
        if (! $teacher) {
            $teacher = User::query()->firstOrCreate(
                ['email' => 'finance.teacher@speakly.com'],
                [
                    'name' => 'Finance Teacher',
                    'password' => 'password',
                    'email_verified_at' => now(),
                    'requested_role' => 'teacher',
                    'approved_at' => now(),
                ]
            );
            $teacher->syncRoles(['teacher']);
        }

        foreach ($children as $index => $child) {
            $assignedCourse = $courses[$index % count($courses)];
            StudentTuition::query()->updateOrCreate(
                ['student_id' => $child->id],
                [
                    'course_id' => $assignedCourse->id,
                    'course_price' => (int) ($assignedCourse->price ?? 32000),
                ]
            );

            $classIds = [];
            $targetCourseCount = $index === 0 ? 4 : 2;
            for ($i = 0; $i < $targetCourseCount; $i++) {
                $course = $courses[$i % count($courses)];
                $class = CourseClass::query()->firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'teacher_id' => $teacher->id,
                    ],
                    ['capacity' => 25]
                );
                $classIds[$class->id] = ['enrolled_at' => now()->subMonths(3)];
            }
            $child->enrolledClasses()->syncWithoutDetaching($classIds);

            for ($w = 0; $w < 8; $w++) {
                AttendanceRecord::query()->updateOrCreate(
                    [
                        'class_id' => array_key_first($classIds),
                        'student_id' => $child->id,
                        'attendance_date' => Carbon::now()->subWeeks(8 - $w)->startOfWeek()->addDay()->toDateString(),
                    ],
                    [
                        'status' => 'present',
                        'grade' => $index === 0 ? 84 : 72,
                        'feedback' => $index === 0 ? 'Strong performance trend.' : 'Needs improvement for scholarship target.',
                    ]
                );
            }

            TuitionPayment::query()->updateOrCreate(
                [
                    'student_id' => $child->id,
                    'reference' => 'PAY-SEED-'.$child->id,
                ],
                [
                    'parent_id' => $parent->id,
                    'recorded_by' => $teacher->id,
                    'amount' => 15000,
                    'paid_on' => now()->subDays(10),
                    'method' => 'cash',
                    'notes' => 'Seed payment for parent financial page test',
                ]
            );
        }

        if ($this->command !== null) {
            $this->command->info('Parent financial scholarship data seeded.');
            $this->command->line('Parent: salahkedja1parent@gmail.com / password');
            $this->command->line('Offer test: first child has 4 courses and high 2-month progress.');
        }
    }
}
