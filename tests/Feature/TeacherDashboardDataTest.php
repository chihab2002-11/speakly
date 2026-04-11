<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Message;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\TeacherResource;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['teacher', 'student'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('uses real class student and schedule aggregates on teacher dashboard', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacher();

    $courseOne = Course::factory()->create(['name' => 'English B2']);
    $courseTwo = Course::factory()->create(['name' => 'IELTS Preparation']);

    $classOne = CourseClass::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $courseOne->id,
    ]);

    $classTwo = CourseClass::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $courseTwo->id,
    ]);

    $room = Room::factory()->create(['name' => 'A101']);

    Schedule::factory()->create([
        'class_id' => $classOne->id,
        'day_of_week' => 'monday',
        'start_time' => '09:00:00',
        'end_time' => '10:30:00',
        'room_id' => $room->id,
    ]);

    Schedule::factory()->create([
        'class_id' => $classTwo->id,
        'day_of_week' => 'tuesday',
        'start_time' => '14:00:00',
        'end_time' => '15:30:00',
        'room_id' => $room->id,
    ]);

    $studentOne = User::factory()->create(['approved_at' => now()]);
    $studentOne->assignRole('student');

    $studentTwo = User::factory()->create(['approved_at' => now()]);
    $studentTwo->assignRole('student');

    $unrelatedStudent = User::factory()->create(['approved_at' => now()]);
    $unrelatedStudent->assignRole('student');

    $nonStudentUser = User::factory()->create(['approved_at' => now()]);
    $nonStudentUser->assignRole('teacher');

    $classOne->students()->attach([$studentOne->id, $studentTwo->id, $nonStudentUser->id]);
    $classTwo->students()->attach([$studentTwo->id]);

    $response = $this->actingAs($teacher)->get(route('role.dashboard', ['role' => 'teacher']));

    $response->assertOk();
    $response->assertViewHas('activeClasses', 2);
    $response->assertViewHas('totalClassesPerWeek', 2);
    $response->assertViewHas('totalStudents', 2);
    $response->assertSee('English B2');
    $response->assertSee('IELTS Preparation');
});

it('uses real unread inbox and notification counts on teacher dashboard', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacher();
    $sender = User::factory()->create(['approved_at' => now()]);
    $sender->assignRole('student');

    Message::query()->create([
        'sender_id' => $sender->id,
        'receiver_id' => $teacher->id,
        'subject' => 'Question',
        'body' => 'Unread message body',
        'read_at' => null,
    ]);

    Message::query()->create([
        'sender_id' => $sender->id,
        'receiver_id' => $teacher->id,
        'subject' => 'Read',
        'body' => 'Read message body',
        'read_at' => now(),
    ]);

    $teacher->notify(new NewMessageNotification(Message::query()->firstOrFail()));

    $response = $this->actingAs($teacher)->get(route('role.dashboard', ['role' => 'teacher']));

    $response->assertOk();
    $response->assertViewHas('unreadMessagesCount', 1);
    $response->assertViewHas('unreadNotificationsCount', 1);
});

it('uses real resource aggregates for quick resource cards', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacher();
    $course = Course::factory()->create();
    $class = CourseClass::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
    ]);

    TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Homework One',
        'description' => null,
        'original_filename' => 'homework-one.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/homework-one.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'download_count' => 5,
    ]);

    TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'name' => 'Material One',
        'description' => null,
        'original_filename' => 'material-one.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/material-one.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 2048,
        'download_count' => 7,
    ]);

    $response = $this->actingAs($teacher)->get(route('role.dashboard', ['role' => 'teacher']));

    $response->assertOk();
    $response->assertSee('Resources (2)');
    $response->assertSee('Homeworks (1)');
    $response->assertSee('Downloads (12)');
    $response->assertSee('Materials (1)');
});
