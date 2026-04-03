# Code Examples & Reference

## 🔑 Key Implementation Files

### TeacherTimetableController.php

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class TeacherTimetableController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Only teachers can view their timetable
        abort_unless($request->user()->hasRole('teacher'), 403);

        $teacher = $request->user();

        // ✅ Get all classes the teacher teaches with relationships
        $taughtClasses = $teacher->taughtClasses()
            ->with([
                'course',
                'schedules.room',
            ])
            ->orderBy('created_at')
            ->get();

        // ✅ Build timetable grouped by day of week
        $timetable = $this->buildTimetable($taughtClasses);

        return view('timetable.teacher', [
            'taughtClasses' => $taughtClasses,
            'timetable' => $timetable,
        ]);
    }

    /**
     * Build timetable grouped by day of week for a teacher
     *
     * @param  Collection  $taughtClasses
     * @return array<string, array>
     */
    private function buildTimetable($taughtClasses): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $timetable = array_fill_keys($days, []);

        foreach ($taughtClasses as $class) {
            foreach ($class->schedules as $schedule) {
                $timetable[$schedule->day_of_week][] = [
                    'course_name' => $class->course->name,
                    'course_code' => $class->course->code,
                    'class_id' => $class->id,
                    'room_name' => $schedule->room?->name ?? 'TBA',
                    'room_capacity' => $schedule->room?->capacity ?? 'N/A',
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'student_count' => $class->students()->count(),
                ];
            }
        }

        // Sort each day's schedule by start time
        foreach ($timetable as &$daySchedules) {
            usort($daySchedules, function ($a, $b) {
                return strtotime($a['start_time']) <=> strtotime($b['start_time']);
            });
        }

        return $timetable;
    }
}
```

### Room Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'capacity', 'location'];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
```

### Schedule Model (Updated)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'day_of_week', 'start_time', 'end_time', 'room_id'];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
```

### Routes Registration

```php
<?php

use App\Http\Controllers\TeacherTimetableController;
use App\Http\Controllers\TimetableController;

Route::middleware([
    'auth',
    'verified',
    EnsureApproved::class,
])->group(function () {
    // ✅ Timetable route for students
    Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');

    // ✅ Timetable route for teachers
    Route::get('/teacher-timetable', [TeacherTimetableController::class, 'index'])->name('timetable.teacher');
});
```

## 📊 Factory Examples

### RoomFactory

```php
<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->bothify('Room ###'),
            'capacity' => fake()->randomElement([20, 25, 30, 35, 40, 50]),
            'location' => fake()->secondaryAddress(),
        ];
    }
}
```

### ScheduleFactory

```php
<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        $startHour = fake()->randomElement([8, 9, 10, 11, 13, 14, 15, 16]);
        $startTime = sprintf('%02d:00', $startHour);
        $endTime = sprintf('%02d:30', $startHour + 1);

        return [
            'class_id' => CourseClass::factory(),
            'day_of_week' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room_id' => Room::factory(),
        ];
    }
}
```

## 🧪 Test Examples

### Basic Teacher Timetable Test

```php
it('teacher can view their timetable', function () {
    // Create roles
    Role::create(['name' => 'teacher', 'guard_name' => 'web']);

    // Create teacher
    $teacher = User::factory()->create([
        'email' => 'teacher@test.com',
        'approved_at' => now(),
    ]);
    $teacher->syncRoles(['teacher']);

    // Create course and class
    $course = Course::factory()->create();
    $class = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    // Create room and schedule
    $room = Room::factory()->create();
    Schedule::factory()->create([
        'class_id' => $class->id,
        'room_id' => $room->id,
        'day_of_week' => 'monday',
    ]);

    $response = $this->actingAs($teacher)->get(route('timetable.teacher'));

    $response->assertStatus(200);
    $response->assertViewIs('timetable.teacher');
    $response->assertViewHas('taughtClasses');
    $response->assertViewHas('timetable');
    $response->assertSee($course->name);
    $response->assertSee($room->name);
});
```

## 🎨 View Examples

### Teacher Timetable View Snippet

```blade
<div class="grid gap-6">
    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
        @if (!empty($timetable[$day]))
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-4 text-lg font-semibold capitalize text-zinc-900 dark:text-white">
                    {{ __($day) }}
                </h3>
                <div class="space-y-3">
                    @foreach ($timetable[$day] as $schedule)
                        <div class="flex items-start justify-between gap-4 border-l-4 border-emerald-500 bg-emerald-50 p-3 dark:border-emerald-400 dark:bg-emerald-900/20">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-zinc-900 dark:text-white">
                                        {{ $schedule['course_name'] }}
                                    </p>
                                    <span class="inline-block rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">
                                        {{ $schedule['course_code'] }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    📍 {{ $schedule['room_name'] }} • Capacity: {{ $schedule['room_capacity'] }}
                                </p>
                                <p class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-400">
                                    👥 {{ $schedule['student_count'] }} {{ Str::plural('student', $schedule['student_count']) }} enrolled
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-zinc-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>
```

## 📦 Migration Examples

### Create Rooms Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('capacity');
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
```

### Modify Schedules Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->dropColumn('room');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
            $table->string('room')->nullable();
        });
    }
};
```

## 🔌 Usage Examples

### Create a Schedule with Room

```php
// In controller or tinker
$room = Room::find(1);
$class = CourseClass::find(1);

$schedule = Schedule::create([
    'class_id' => $class->id,
    'day_of_week' => 'monday',
    'start_time' => '09:00',
    'end_time' => '10:30',
    'room_id' => $room->id,
]);

// Access related data
echo $schedule->room->name;        // "Room 101"
echo $schedule->room->capacity;    // 30
echo $schedule->class->course->name; // "Math 101"
```

### Query with Eager Loading

```php
// Get all schedules with room details (no N+1 queries)
$schedules = Schedule::with('room')->get();

// For teacher's classes
$teacher = User::find(1);
$classes = $teacher->taughtClasses()
    ->with(['course', 'schedules.room', 'students'])
    ->get();

foreach ($classes as $class) {
    foreach ($class->schedules as $schedule) {
        echo $schedule->room->name; // No additional queries!
    }
}
```

### Authorization Check

```php
// In controller
public function index(Request $request)
{
    abort_unless($request->user()->hasRole('teacher'), 403);
    
    // Rest of the code...
}

// Returns 403 Forbidden if user is not a teacher
```

## 📋 Command Reference

```bash
# Run all tests
php artisan test --compact

# Run only teacher timetable tests
php artisan test --compact tests/Feature/TeacherTimetableTest.php

# Seed rooms data
php artisan db:seed --class=RoomSeeder

# Format code
vendor/bin/pint --dirty

# Create test data in tinker
php artisan tinker
> Course::factory(5)->create();
> CourseClass::factory(3)->create();
> Room::factory(10)->create();
> Schedule::factory(15)->create();

# View routes
php artisan route:list
```

---

**Last Updated**: April 3, 2026  
**Version**: 1.0.0

