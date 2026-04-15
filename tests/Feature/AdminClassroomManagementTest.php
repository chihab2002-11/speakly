<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['admin', 'teacher'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

function createAdminForClassroomTests(): User
{
    $admin = User::factory()->create([
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    return $admin;
}

test('admin can view manage classrooms page', function () {
    /** @var TestCase $this */
    $admin = createAdminForClassroomTests();

    $this->actingAs($admin)
        ->get(route('admin.classrooms.index'))
        ->assertOk()
        ->assertSee('Manage Classrooms')
        ->assertSee('Add New Classroom');
});

test('admin can create classroom in rooms table', function () {
    /** @var TestCase $this */
    $admin = createAdminForClassroomTests();

    $this->actingAs($admin)
        ->post(route('admin.classrooms.store'), [
            'name' => 'Room 404',
        ])
        ->assertRedirect(route('admin.classrooms.index'));

    $this->assertDatabaseHas('rooms', [
        'name' => 'Room 404',
        'location' => null,
    ]);
});

test('admin can update classroom', function () {
    /** @var TestCase $this */
    $admin = createAdminForClassroomTests();
    $room = Room::factory()->create([
        'name' => 'Room Alpha',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.classrooms.update', $room), [
            'name' => 'Room Alpha Updated',
        ])
        ->assertRedirect(route('admin.classrooms.index'));

    $room->refresh();
    expect($room->name)->toBe('Room Alpha Updated');
    expect($room->location)->toBeNull();
});

test('admin cannot delete classroom used in schedule', function () {
    /** @var TestCase $this */
    $admin = createAdminForClassroomTests();
    $room = Room::factory()->create();

    $teacher = User::factory()->create(['approved_at' => now()]);
    $teacher->assignRole('teacher');

    $course = Course::factory()->create();
    $class = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    Schedule::factory()->create([
        'class_id' => $class->id,
        'room_id' => $room->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.classrooms.destroy', $room))
        ->assertRedirect(route('admin.classrooms.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('rooms', ['id' => $room->id]);
});
