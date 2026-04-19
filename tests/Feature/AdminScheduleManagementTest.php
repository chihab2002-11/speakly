<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedAuthorizationFixtures();
});

function createApprovedAdmin(): User
{
    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    return $admin;
}

test('admin can view manage schedule page', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdmin();

    $response = $this->actingAs($admin)->get(route('admin.schedule.index'));

    $response
        ->assertOk()
        ->assertSee('Manage Schedule')
        ->assertSee('Add Schedule Slot')
        ->assertSee('Timetable Hub');
});

test('admin can open schedule timetable hub page', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdmin();

    $response = $this->actingAs($admin)->get(route('admin.schedule.timetable-hub'));

    $response
        ->assertOk()
        ->assertSee('Timetable Hub')
        ->assertSee('Back to Manage Schedule');
});

test('admin can create schedule slot', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdmin();

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $room = Room::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.schedule.store'), [
        'class_id' => $group->id,
        'room_id' => $room->id,
        'day_of_week' => 'monday',
        'start_time' => '09:00',
        'end_time' => '10:30',
    ]);

    $response->assertRedirect(route('admin.schedule.index'));

    $this->assertDatabaseHas('schedules', [
        'class_id' => $group->id,
        'room_id' => $room->id,
        'day_of_week' => 'monday',
    ]);
});

test('admin cannot create room conflict schedule slot', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdmin();

    $teacherA = User::factory()->create(['approved_at' => now()]);
    $teacherA->assignRole('teacher');
    $teacherB = User::factory()->create(['approved_at' => now()]);
    $teacherB->assignRole('teacher');

    $courseA = Course::factory()->create();
    $courseB = Course::factory()->create();
    $groupA = CourseClass::factory()->create([
        'course_id' => $courseA->id,
        'teacher_id' => $teacherA->id,
    ]);
    $groupB = CourseClass::factory()->create([
        'course_id' => $courseB->id,
        'teacher_id' => $teacherB->id,
    ]);

    $room = Room::factory()->create();

    $this->actingAs($admin)->post(route('admin.schedule.store'), [
        'class_id' => $groupA->id,
        'room_id' => $room->id,
        'day_of_week' => 'tuesday',
        'start_time' => '10:00',
        'end_time' => '11:30',
    ])->assertRedirect(route('admin.schedule.index'));

    $response = $this->actingAs($admin)->post(route('admin.schedule.store'), [
        'class_id' => $groupB->id,
        'room_id' => $room->id,
        'day_of_week' => 'tuesday',
        'start_time' => '10:30',
        'end_time' => '12:00',
    ]);

    $response->assertSessionHasErrors('room_id');
});

test('admin cannot create teacher conflict schedule slot', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdmin();

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $courseA = Course::factory()->create();
    $courseB = Course::factory()->create();
    $groupA = CourseClass::factory()->create([
        'course_id' => $courseA->id,
        'teacher_id' => $teacher->id,
    ]);
    $groupB = CourseClass::factory()->create([
        'course_id' => $courseB->id,
        'teacher_id' => $teacher->id,
    ]);
    $roomA = Room::factory()->create();
    $roomB = Room::factory()->create();

    $this->actingAs($admin)->post(route('admin.schedule.store'), [
        'class_id' => $groupA->id,
        'room_id' => $roomA->id,
        'day_of_week' => 'wednesday',
        'start_time' => '08:00',
        'end_time' => '09:30',
    ])->assertRedirect(route('admin.schedule.index'));

    $response = $this->actingAs($admin)->post(route('admin.schedule.store'), [
        'class_id' => $groupB->id,
        'room_id' => $roomB->id,
        'day_of_week' => 'wednesday',
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);

    $response->assertSessionHasErrors('class_id');
});

test('admin can update and delete schedule slot', function () {
    /** @var TestCase $this */
    $admin = createApprovedAdmin();

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');
    $course = Course::factory()->create();
    $group = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $room = Room::factory()->create();

    $this->actingAs($admin)->post(route('admin.schedule.store'), [
        'class_id' => $group->id,
        'room_id' => $room->id,
        'day_of_week' => 'friday',
        'start_time' => '14:00',
        'end_time' => '15:30',
    ])->assertRedirect(route('admin.schedule.index'));

    $schedule = Schedule::query()->latest('id')->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.schedule.update', $schedule), [
            'class_id' => $group->id,
            'room_id' => $room->id,
            'day_of_week' => 'friday',
            'start_time' => '15:00',
            'end_time' => '16:30',
        ])
        ->assertRedirect(route('admin.schedule.index'));

    $schedule->refresh();
    expect((string) $schedule->start_time)->toContain('15:00');

    $this->actingAs($admin)
        ->delete(route('admin.schedule.destroy', $schedule))
        ->assertRedirect(route('admin.schedule.index'));

    $this->assertDatabaseMissing('schedules', [
        'id' => $schedule->id,
    ]);
});
