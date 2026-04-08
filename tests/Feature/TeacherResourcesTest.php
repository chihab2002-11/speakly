<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\TeacherResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('teacher', 'web');
});

function createApprovedTeacher(): User
{
    $teacher = User::factory()->create([
        'approved_at' => now(),
    ]);

    $teacher->assignRole('teacher');

    return $teacher;
}

function createClassForTeacher(User $teacher): CourseClass
{
    $course = Course::factory()->create();

    return CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
}

it('teacher can upload a resource for classes they teach', function () {
    Storage::fake('public');

    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    $response = $this->actingAs($teacher)->post(route('teacher.resources.store'), [
        'file' => UploadedFile::fake()->create('worksheet.pdf', 150, 'application/pdf'),
        'class_id' => $class->id,
        'name' => 'Worksheet Week 1',
        'category_id' => TeacherResource::CATEGORY_HOMEWORK,
        'description' => 'Intro worksheet',
    ]);

    $response->assertRedirect(route('teacher.resources'));

    $resource = TeacherResource::query()->first();

    expect($resource)->not()->toBeNull();
    expect($resource->teacher_id)->toBe($teacher->id);
    expect($resource->class_id)->toBe($class->id);
    expect($resource->category)->toBe(TeacherResource::CATEGORY_HOMEWORK);

    Storage::disk('public')->assertExists($resource->file_path);
});

it('teacher can upload a pdf successfully end to end', function () {
    Storage::fake('public');

    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    $response = $this->actingAs($teacher)->post(route('teacher.resources.store'), [
        'file' => UploadedFile::fake()->create('lesson-plan.pdf', 200, 'application/pdf'),
        'class_id' => $class->id,
        'name' => 'Lesson Plan',
        'category_id' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'description' => 'PDF upload verification',
    ]);

    $response->assertRedirect(route('teacher.resources'));

    $resource = TeacherResource::query()->where('name', 'Lesson Plan')->first();

    expect($resource)->not()->toBeNull();
    expect($resource->teacher_id)->toBe($teacher->id);
    expect($resource->class_id)->toBe($class->id);
    expect($resource->original_filename)->toBe('lesson-plan.pdf');
    expect(str_starts_with($resource->file_path, 'teacher-resources/'.$teacher->id.'/'))->toBeTrue();

    Storage::disk('public')->assertExists($resource->file_path);
});

it('teacher cannot upload resources for classes they do not teach', function () {
    Storage::fake('public');

    $teacher = createApprovedTeacher();
    $otherTeacher = createApprovedTeacher();
    $otherClass = createClassForTeacher($otherTeacher);

    $response = $this->actingAs($teacher)->post(route('teacher.resources.store'), [
        'file' => UploadedFile::fake()->create('worksheet.pdf', 150, 'application/pdf'),
        'class_id' => $otherClass->id,
        'name' => 'Not Allowed Upload',
        'category_id' => TeacherResource::CATEGORY_HOMEWORK,
        'description' => 'Should fail',
    ]);

    $response->assertSessionHasErrors('class_id');
    expect(TeacherResource::query()->count())->toBe(0);
});

it('invalid upload requests are rejected', function () {
    Storage::fake('public');

    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    $response = $this->actingAs($teacher)->post(route('teacher.resources.store'), [
        'file' => UploadedFile::fake()->create('script.exe', 10),
        'class_id' => $class->id,
        'name' => 'Bad Resource',
        'category_id' => 'invalid_category',
        'description' => 'Should fail',
    ]);

    $response->assertSessionHasErrors(['file', 'category_id']);
    expect(TeacherResource::query()->count())->toBe(0);
});

it('shows a clear error when php rejects an oversized upload before validation', function () {
    Storage::fake('public');

    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    $tempFilePath = tempnam(sys_get_temp_dir(), 'upload-limit-');
    file_put_contents($tempFilePath, 'stub-pdf-content');

    $failedUpload = new UploadedFile(
        $tempFilePath,
        'oversized.pdf',
        'application/pdf',
        UPLOAD_ERR_INI_SIZE,
        true,
    );

    $response = $this->actingAs($teacher)->post(route('teacher.resources.store'), [
        'file' => $failedUpload,
        'class_id' => $class->id,
        'name' => 'Oversized Upload',
        'category_id' => TeacherResource::CATEGORY_HOMEWORK,
        'description' => 'Should fail before validator max rule.',
    ]);

    if (is_string($tempFilePath) && is_file($tempFilePath)) {
        unlink($tempFilePath);
    }

    $response->assertSessionHasErrors('file');
    expect(session('errors')->first('file'))->toContain('exceeds the server upload limit');
    expect(TeacherResource::query()->count())->toBe(0);
});

it('teacher can update own resource', function () {
    $teacher = createApprovedTeacher();
    $initialClass = createClassForTeacher($teacher);
    $newClass = createClassForTeacher($teacher);

    $resource = TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $initialClass->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Original Name',
        'description' => 'Original description',
        'original_filename' => 'original.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/original.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'download_count' => 4,
    ]);

    $response = $this->actingAs($teacher)->patch(route('teacher.resources.update', $resource->id), [
        'class_id' => $newClass->id,
        'name' => 'Updated Name',
        'category_id' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'description' => 'Updated description',
    ]);

    $response->assertRedirect(route('teacher.resources'));

    $resource->refresh();

    expect($resource->class_id)->toBe($newClass->id);
    expect($resource->name)->toBe('Updated Name');
    expect($resource->category)->toBe(TeacherResource::CATEGORY_COURSE_MATERIALS);
    expect($resource->description)->toBe('Updated description');
    expect($resource->original_filename)->toBe('original.pdf');
    expect($resource->file_path)->toBe('teacher-resources/'.$teacher->id.'/original.pdf');
});

it('teacher cannot update another teachers resource', function () {
    $teacher = createApprovedTeacher();
    $otherTeacher = createApprovedTeacher();
    $otherClass = createClassForTeacher($otherTeacher);

    $resource = TeacherResource::query()->create([
        'teacher_id' => $otherTeacher->id,
        'class_id' => $otherClass->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Private Resource',
        'description' => 'Should stay private',
        'original_filename' => 'private.pdf',
        'file_path' => 'teacher-resources/'.$otherTeacher->id.'/private.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 2048,
        'download_count' => 0,
    ]);

    $response = $this->actingAs($teacher)->patch(route('teacher.resources.update', $resource->id), [
        'class_id' => $otherClass->id,
        'name' => 'Tampered Name',
        'category_id' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'description' => 'Tampered description',
    ]);

    $response->assertForbidden();

    expect($resource->fresh()->name)->toBe('Private Resource');
    expect($resource->fresh()->description)->toBe('Should stay private');
});

it('invalid update payload is rejected', function () {
    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    $otherTeacher = createApprovedTeacher();
    $otherClass = createClassForTeacher($otherTeacher);

    $resource = TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Stable Resource',
        'description' => 'Stable description',
        'original_filename' => 'stable.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/stable.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1500,
        'download_count' => 2,
    ]);

    $response = $this->actingAs($teacher)->patch(route('teacher.resources.update', $resource->id), [
        'class_id' => $otherClass->id,
        'name' => '',
        'category_id' => 'invalid-category',
        'description' => str_repeat('x', 1200),
    ]);

    $response->assertSessionHasErrors(['class_id', 'name', 'category_id', 'description']);

    $resource->refresh();
    expect($resource->class_id)->toBe($class->id);
    expect($resource->name)->toBe('Stable Resource');
    expect($resource->category)->toBe(TeacherResource::CATEGORY_HOMEWORK);
    expect($resource->description)->toBe('Stable description');
});

it('teacher can download own resource and increments download count', function () {
    Storage::fake('public');

    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    $filePath = 'teacher-resources/'.$teacher->id.'/handout.pdf';
    Storage::disk('public')->put($filePath, 'dummy-pdf-content');

    $resource = TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'name' => 'Handout',
        'description' => null,
        'original_filename' => 'handout.pdf',
        'file_path' => $filePath,
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'download_count' => 0,
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.resources.download', $resource->id));

    $response->assertOk();
    expect($resource->fresh()->download_count)->toBe(1);
});

it('teacher cannot download or delete another teachers resource', function () {
    Storage::fake('public');

    $teacher = createApprovedTeacher();
    $otherTeacher = createApprovedTeacher();
    $otherClass = createClassForTeacher($otherTeacher);

    $filePath = 'teacher-resources/'.$otherTeacher->id.'/private.pdf';
    Storage::disk('public')->put($filePath, 'private-content');

    $resource = TeacherResource::query()->create([
        'teacher_id' => $otherTeacher->id,
        'class_id' => $otherClass->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Private Resource',
        'description' => null,
        'original_filename' => 'private.pdf',
        'file_path' => $filePath,
        'mime_type' => 'application/pdf',
        'file_size' => 2048,
        'download_count' => 0,
    ]);

    $this->actingAs($teacher)
        ->get(route('teacher.resources.download', $resource->id))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->delete(route('teacher.resources.destroy', $resource->id))
        ->assertForbidden();

    $this->assertDatabaseHas('teacher_resources', ['id' => $resource->id]);
    Storage::disk('public')->assertExists($filePath);
});

it('teacher can delete own resource and file', function () {
    Storage::fake('public');

    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    $filePath = 'teacher-resources/'.$teacher->id.'/deletable.pdf';
    Storage::disk('public')->put($filePath, 'delete-me');

    $resource = TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Deletable Resource',
        'description' => null,
        'original_filename' => 'deletable.pdf',
        'file_path' => $filePath,
        'mime_type' => 'application/pdf',
        'file_size' => 4096,
        'download_count' => 0,
    ]);

    $response = $this->actingAs($teacher)->delete(route('teacher.resources.destroy', $resource->id));

    $response->assertRedirect(route('teacher.resources'));
    $this->assertDatabaseMissing('teacher_resources', ['id' => $resource->id]);
    Storage::disk('public')->assertMissing($filePath);
});

it('teacher can search and filter resources by category and file type', function () {
    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Grammar Worksheet Week 2',
        'description' => null,
        'original_filename' => 'grammar-week-2.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/grammar-week-2.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'download_count' => 3,
    ]);

    TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'name' => 'Conversation Prompts',
        'description' => null,
        'original_filename' => 'conversation-prompts.docx',
        'file_path' => 'teacher-resources/'.$teacher->id.'/conversation-prompts.docx',
        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'file_size' => 2048,
        'download_count' => 9,
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.resources', [
        'search' => 'Grammar',
        'category_id' => TeacherResource::CATEGORY_HOMEWORK,
        'file_type' => 'pdf',
    ]));

    $response->assertOk();
    $response->assertSee('Grammar Worksheet Week 2');
    $response->assertDontSee('Conversation Prompts');
});

it('teacher can filter resources by class', function () {
    $teacher = createApprovedTeacher();
    $classOne = createClassForTeacher($teacher);
    $classTwo = createClassForTeacher($teacher);

    TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $classOne->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Class One Resource',
        'description' => null,
        'original_filename' => 'class-one.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/class-one.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'download_count' => 1,
    ]);

    TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $classTwo->id,
        'category' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'name' => 'Class Two Resource',
        'description' => null,
        'original_filename' => 'class-two.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/class-two.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'download_count' => 1,
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.resources', [
        'class_id' => (string) $classOne->id,
    ]));

    $response->assertOk();
    $response->assertSee('Class One Resource');
    $response->assertDontSee('Class Two Resource');
});

it('teacher can sort resources by downloads descending', function () {
    $teacher = createApprovedTeacher();
    $class = createClassForTeacher($teacher);

    TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'Low Downloads',
        'description' => null,
        'original_filename' => 'low.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/low.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'download_count' => 1,
    ]);

    TeacherResource::query()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_HOMEWORK,
        'name' => 'High Downloads',
        'description' => null,
        'original_filename' => 'high.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/high.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'download_count' => 25,
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.resources', [
        'sort_by' => 'downloads',
    ]));

    $response->assertOk();
    $response->assertSeeInOrder(['High Downloads', 'Low Downloads']);
});
