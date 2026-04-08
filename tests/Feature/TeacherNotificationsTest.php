<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Message;
use App\Models\TeacherResource;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use App\Notifications\TeacherAttendanceSavedNotification;
use App\Notifications\TeacherResourceActionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['teacher', 'student'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

function createApprovedTeacherForNotifications(): User
{
    $teacher = User::factory()->create([
        'approved_at' => now(),
    ]);

    $teacher->assignRole('teacher');

    return $teacher;
}

function createClassForTeacherNotifications(User $teacher): CourseClass
{
    $course = Course::factory()->create();

    return CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
}

it('creates a resource notification when teacher uploads a resource', function () {
    /** @var TestCase $this */
    Storage::fake('public');

    $teacher = createApprovedTeacherForNotifications();
    $class = createClassForTeacherNotifications($teacher);

    $response = $this->actingAs($teacher)->post(route('teacher.resources.store'), [
        'file' => UploadedFile::fake()->create('lesson-notes.pdf', 120, 'application/pdf'),
        'class_id' => $class->id,
        'name' => 'Lesson Notes',
        'category_id' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'description' => 'Chapter one notes',
    ]);

    $response->assertRedirect(route('teacher.resources'));

    $notification = $teacher->fresh()->notifications()->latest()->first();

    expect($notification)->not()->toBeNull();
    expect($notification->type)->toBe(TeacherResourceActionNotification::class);
    expect($notification->data['type'])->toBe('resource');
    expect($notification->data['action'])->toBe('uploaded');
    expect($notification->data['resource_name'])->toBe('Lesson Notes');
    expect($notification->data['url'])->toBe(route('teacher.resources'));
});

it('creates a resource notification when teacher deletes a resource', function () {
    /** @var TestCase $this */
    Storage::fake('public');

    $teacher = createApprovedTeacherForNotifications();
    $class = createClassForTeacherNotifications($teacher);

    $filePath = 'teacher-resources/'.$teacher->id.'/old-resource.pdf';
    Storage::disk('public')->put($filePath, 'obsolete-content');

    $resource = TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Old Resource',
        'description' => null,
        'original_filename' => 'old-resource.pdf',
        'file_path' => $filePath,
        'mime_type' => 'application/pdf',
        'file_size' => 2048,
        'download_count' => 0,
    ]);

    $response = $this->actingAs($teacher)->delete(route('teacher.resources.destroy', $resource->id));

    $response->assertRedirect(route('teacher.resources'));

    $notification = $teacher->fresh()->notifications()->latest()->first();

    expect($notification)->not()->toBeNull();
    expect($notification->type)->toBe(TeacherResourceActionNotification::class);
    expect($notification->data['type'])->toBe('resource');
    expect($notification->data['action'])->toBe('deleted');
    expect($notification->data['resource_name'])->toBe('Old Resource');
});

it('stores message notifications with explicit message type metadata', function () {
    $teacher = createApprovedTeacherForNotifications();

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $message = Message::query()->create([
        'sender_id' => $student->id,
        'receiver_id' => $teacher->id,
        'subject' => 'Question',
        'body' => 'Can we review chapter two?',
    ]);

    $teacher->notify(new NewMessageNotification($message));

    $notification = $teacher->fresh()->notifications()->latest()->first();

    expect($notification)->not()->toBeNull();
    expect($notification->data['type'])->toBe('message');
    expect($notification->data['message_id'])->toBe($message->id);
});

it('creates an attendance notification when teacher saves attendance', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForNotifications();
    $class = createClassForTeacherNotifications($teacher);

    $student = User::factory()->create([
        'approved_at' => now(),
    ]);
    $student->assignRole('student');

    $class->students()->attach([$student->id]);

    $date = '2026-04-08';

    $response = $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
        'class_id' => $class->id,
        'date' => $date,
        'records' => [
            [
                'student_id' => $student->id,
                'status' => 'present',
                'grade' => 91,
                'feedback' => 'Strong participation',
            ],
        ],
    ]);

    $response->assertRedirect(route('teacher.attendance', [
        'class_id' => $class->id,
        'date' => $date,
    ]));

    $notification = $teacher->fresh()->notifications()->latest()->first();

    expect($notification)->not()->toBeNull();
    expect($notification->type)->toBe(TeacherAttendanceSavedNotification::class);
    expect($notification->data['type'])->toBe('attendance');
    expect($notification->data['action'])->toBe('saved');
    expect($notification->data['class_id'])->toBe($class->id);
    expect($notification->data['attendance_date'])->toBe($date);
    expect($notification->data['records_count'])->toBe(1);
    expect($notification->data['url'])->toBe(route('teacher.attendance', [
        'class_id' => $class->id,
        'date' => $date,
    ]));
});

it('keeps teacher notification read endpoints working', function () {
    /** @var TestCase $this */
    $teacher = createApprovedTeacherForNotifications();

    $teacher->notify(new TeacherResourceActionNotification(
        action: 'uploaded',
        resourceName: 'Worksheet One',
        className: 'English B2',
    ));

    $teacher->notify(new TeacherResourceActionNotification(
        action: 'uploaded',
        resourceName: 'Worksheet Two',
        className: 'English B2',
    ));

    $indexResponse = $this->actingAs($teacher)->get(route('teacher.notifications'));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Resource uploaded');

    $notificationId = $teacher->fresh()->unreadNotifications()->latest()->firstOrFail()->id;

    $this->actingAs($teacher)
        ->post(route('teacher.notifications.read', $notificationId))
        ->assertRedirect();

    expect($teacher->fresh()->unreadNotifications()->count())->toBe(1);

    $this->actingAs($teacher)
        ->post(route('teacher.notifications.read-all'))
        ->assertRedirect();

    expect($teacher->fresh()->unreadNotifications()->count())->toBe(0);
});
