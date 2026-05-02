<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Message;
use App\Models\TeacherResource;
use App\Models\User;
use App\Notifications\ClassResourceUploadedNotification;
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
    foreach (['teacher', 'student', 'parent'] as $role) {
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

it('notifies enrolled students and linked parents when teacher uploads homework', function () {
    /** @var TestCase $this */
    Storage::fake('public');

    $teacher = createApprovedTeacherForNotifications();
    $class = createClassForTeacherNotifications($teacher);
    $class->course->update([
        'name' => 'English',
        'code' => 'A1',
    ]);
    $deadline = now()->addWeek()->toDateString();

    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $student = User::factory()->create([
        'name' => 'Karim',
        'approved_at' => now(),
        'parent_id' => $parent->id,
    ]);
    $student->assignRole('student');

    $outsideParent = User::factory()->create(['approved_at' => now()]);
    $outsideParent->assignRole('parent');

    $outsideStudent = User::factory()->create([
        'approved_at' => now(),
        'parent_id' => $outsideParent->id,
    ]);
    $outsideStudent->assignRole('student');

    $class->students()->attach([$student->id]);

    $response = $this->actingAs($teacher)->post(route('teacher.resources.store'), [
        'file' => UploadedFile::fake()->create('homework.pdf', 120, 'application/pdf'),
        'class_id' => $class->id,
        'name' => 'Homework Week 1',
        'category_id' => TeacherResource::CATEGORY_HOMEWORK,
        'deadline' => $deadline,
        'description' => 'Read chapter one.',
    ]);

    $response->assertRedirect(route('teacher.resources'));

    $studentNotification = $student->fresh()->notifications()->latest()->first();
    $parentNotification = $parent->fresh()->notifications()->latest()->first();

    expect($studentNotification)->not()->toBeNull();
    expect($studentNotification->type)->toBe(ClassResourceUploadedNotification::class);
    expect($studentNotification->data['type'])->toBe('homework_uploaded');
    expect($studentNotification->data['title'])->toBe('New homework uploaded');
    expect($studentNotification->data['message'])->toContain('English A1 - Group #'.$class->id);
    expect($studentNotification->data['message'])->toContain('Deadline: '.$deadline);
    expect($studentNotification->data['message'])->not->toContain('your child');
    expect($studentNotification->data['url'])->toBe(route('student.materials'));

    expect($parentNotification)->not()->toBeNull();
    expect($parentNotification->type)->toBe(ClassResourceUploadedNotification::class);
    expect($parentNotification->data['title'])->toBe('New homework for your child');
    expect($parentNotification->data['message'])->toContain('your child Karim');
    expect($parentNotification->data['message'])->toContain('Deadline: '.$deadline);
    expect($parentNotification->data['child_id'])->toBe($student->id);
    expect($parentNotification->data['url'])->toBe(route('parent.child.materials', ['child' => $student->id]));

    expect($outsideStudent->fresh()->notifications()->count())->toBe(0);
    expect($outsideParent->fresh()->notifications()->count())->toBe(0);
});

it('notifies enrolled students and linked parents when teacher uploads a course resource', function () {
    /** @var TestCase $this */
    Storage::fake('public');

    $teacher = createApprovedTeacherForNotifications();
    $class = createClassForTeacherNotifications($teacher);

    $parent = User::factory()->create(['approved_at' => now()]);
    $parent->assignRole('parent');

    $student = User::factory()->create([
        'name' => 'Lina',
        'approved_at' => now(),
        'parent_id' => $parent->id,
    ]);
    $student->assignRole('student');
    $class->students()->attach([$student->id]);

    $response = $this->actingAs($teacher)->post(route('teacher.resources.store'), [
        'file' => UploadedFile::fake()->create('lesson.pdf', 120, 'application/pdf'),
        'class_id' => $class->id,
        'name' => 'Lesson Notes',
        'category_id' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'description' => 'Chapter one notes',
    ]);

    $response->assertRedirect(route('teacher.resources'));

    $studentNotification = $student->fresh()->notifications()->latest()->first();
    $parentNotification = $parent->fresh()->notifications()->latest()->first();

    expect($studentNotification)->not()->toBeNull();
    expect($studentNotification->data['type'])->toBe('class_resource_uploaded');
    expect($studentNotification->data['title'])->toBe('New course resource uploaded');
    expect($studentNotification->data['message'])->toContain('A new course resource was uploaded');
    expect($studentNotification->data['deadline'])->toBeNull();

    expect($parentNotification)->not()->toBeNull();
    expect($parentNotification->data['type'])->toBe('class_resource_uploaded');
    expect($parentNotification->data['title'])->toBe('New course resource for your child');
    expect($parentNotification->data['message'])->toContain('your child Lina');
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
    scheduleClassOnDate($class, $date);

    $response = $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
        'class_id' => $class->id,
        'date' => $date,
        'submit_action' => 'save_all',
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
