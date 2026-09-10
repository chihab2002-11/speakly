<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\TeacherResource;
use App\Models\User;
use App\Services\Ai\AiMaterialExplainerService;
use App\Services\Ai\AiProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['teacher', 'student', 'parent'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

function createAiMaterialUser(string $role): User
{
    $user = User::factory()->create([
        'approved_at' => now(),
        'requested_role' => $role,
    ]);

    $user->assignRole($role);

    return $user;
}

function createAiMaterialResource(User $teacher, array $attributes = []): array
{
    $course = Course::factory()->create([
        'name' => 'English A1',
        'code' => 'ENG-A1',
    ]);

    $class = CourseClass::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $resource = TeacherResource::query()->create(array_merge([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'category' => TeacherResource::CATEGORY_COURSE_MATERIALS,
        'name' => 'Simple Grammar Notes',
        'description' => 'Teacher notes about present simple verbs.',
        'original_filename' => 'grammar-notes.txt',
        'file_path' => 'teacher-resources/'.$teacher->id.'/grammar-notes.txt',
        'mime_type' => 'text/plain',
        'file_size' => 1024,
        'download_count' => 0,
    ], $attributes));

    return [$class, $resource];
}

it('streams an ai material explanation for an enrolled student', function () {
    Storage::fake('public');

    $teacher = createAiMaterialUser('teacher');
    $student = createAiMaterialUser('student');
    [$class, $resource] = createAiMaterialResource($teacher);

    Storage::disk('public')->put($resource->file_path, 'Students use present simple for habits and routines.');
    $student->enrolledClasses()->attach($class->id, ['enrolled_at' => now()]);

    $provider = new class implements AiProviderInterface
    {
        public string $receivedText = '';

        public function streamMaterialExplanation(TeacherResource $resource, string $textContent): Generator
        {
            $this->receivedText = $textContent;

            yield 'Simple ';
            yield 'Summary';
        }
    };

    $this->app->instance(AiProviderInterface::class, $provider);

    $response = $this->actingAs($student)
        ->post(route('student.materials.ai-explain', $resource));

    $response->assertOk();

    expect($response->streamedContent())->toBe('Simple Summary');
    expect($provider->receivedText)
        ->toContain('Teacher notes about present simple verbs.')
        ->toContain('Students use present simple for habits and routines.');
});

it('blocks ai explanations for resources outside the student enrollment', function () {
    $teacher = createAiMaterialUser('teacher');
    $student = createAiMaterialUser('student');
    [, $resource] = createAiMaterialResource($teacher);

    $provider = new class implements AiProviderInterface
    {
        public bool $called = false;

        public function streamMaterialExplanation(TeacherResource $resource, string $textContent): Generator
        {
            $this->called = true;

            yield 'blocked';
        }
    };

    $this->app->instance(AiProviderInterface::class, $provider);

    $this->actingAs($student)
        ->post(route('student.materials.ai-explain', $resource))
        ->assertForbidden();

    expect($provider->called)->toBeFalse();
});

it('streams an ai material explanation using metadata fallback when no readable material text exists', function () {
    $teacher = createAiMaterialUser('teacher');
    $student = createAiMaterialUser('student');
    [$class, $resource] = createAiMaterialResource($teacher, [
        'name' => 'Grammar PDF Worksheet',
        'description' => null,
        'original_filename' => 'worksheet.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/worksheet.pdf',
        'mime_type' => 'application/pdf',
    ]);

    $student->enrolledClasses()->attach($class->id, ['enrolled_at' => now()]);

    $provider = new class implements AiProviderInterface
    {
        public string $receivedText = '';

        public function streamMaterialExplanation(TeacherResource $resource, string $textContent): Generator
        {
            $this->receivedText = $textContent;

            yield 'Metadata fallback response';
        }
    };

    $this->app->instance(AiProviderInterface::class, $provider);

    $response = $this->actingAs($student)
        ->post(route('student.materials.ai-explain', $resource));

    $response->assertOk();
    expect($response->streamedContent())->toBe('Metadata fallback response');
    expect($provider->receivedText)->toContain('Resource Title: Grammar PDF Worksheet');
});

it('uses plain text content from pdf-like files when the file content is readable', function () {
    Storage::fake('public');

    $teacher = createAiMaterialUser('teacher');
    $student = createAiMaterialUser('student');
    [$class, $resource] = createAiMaterialResource($teacher, [
        'name' => 'French B1 Verb Map',
        'description' => null,
        'original_filename' => 'french-b1-verb-map.pdf',
        'file_path' => 'teacher-resources/'.$teacher->id.'/french-b1-verb-map.pdf',
        'mime_type' => 'application/pdf',
    ]);

    Storage::disk('public')->put($resource->file_path, "This is the actual lesson text from the PDF placeholder.\nIt should be available to the AI.");
    $student->enrolledClasses()->attach($class->id, ['enrolled_at' => now()]);

    $provider = new class implements AiProviderInterface
    {
        public string $receivedText = '';

        public function streamMaterialExplanation(TeacherResource $resource, string $textContent): Generator
        {
            $this->receivedText = $textContent;

            yield 'Using text content';
        }
    };

    $this->app->instance(AiProviderInterface::class, $provider);

    $response = $this->actingAs($student)
        ->post(route('student.materials.ai-explain', $resource));

    $response->assertOk();
    expect($response->streamedContent())->toBe('Using text content');
    expect($provider->receivedText)->toContain('This is the actual lesson text from the PDF placeholder');
    expect($provider->receivedText)->toContain('--- PDF Document Content ---');
});

it('parses streamed ollama json response chunks', function () {
    Http::fake([
        'http://127.0.0.1:11434/api/generate' => Http::response(
            '{"response":"Hello ","done":false}'."\n".
            '{"response":"student","done":false}'."\n".
            '{"done":true}'."\n",
            200
        ),
    ]);

    $teacher = createAiMaterialUser('teacher');
    [, $resource] = createAiMaterialResource($teacher);
    $service = new AiMaterialExplainerService;

    $result = collect($service->streamMaterialExplanation($resource, 'Present simple lesson text.'))->implode('');

    expect($result)->toBe('Hello student');

    Http::assertSent(fn ($request): bool => $request->url() === 'http://127.0.0.1:11434/api/generate'
        && $request['model'] === 'qwen3:4b'
        && $request['stream'] === true
        && $request['think'] === false
        && str_contains((string) $request['prompt'], '## Key Vocabulary'));
});

it('streams an ai material explanation from parent child portal routes', function () {
    Storage::fake('public');

    $teacher = createAiMaterialUser('teacher');
    $parent = createAiMaterialUser('parent');
    $child = createAiMaterialUser('student');
    $child->update(['parent_id' => $parent->id]);
    [$class, $resource] = createAiMaterialResource($teacher);

    Storage::disk('public')->put($resource->file_path, 'Present simple is used for habits.');
    $child->enrolledClasses()->attach($class->id, ['enrolled_at' => now()]);

    $provider = new class implements AiProviderInterface
    {
        public function streamMaterialExplanation(TeacherResource $resource, string $textContent): Generator
        {
            yield str_contains($textContent, 'Present simple is used for habits.')
                ? 'Parent portal explanation'
                : 'Missing resource text';
        }
    };

    $this->app->instance(AiProviderInterface::class, $provider);

    $response = $this->actingAs($parent)
        ->post(route('parent.child.materials.ai-explain', [
            'child' => $child->id,
            'resource' => $resource->id,
        ]));

    $response->assertOk();
    expect($response->streamedContent())->toBe('Parent portal explanation');
});

it('falls back when ollama returns no usable chunks', function () {
    Http::fake([
        'http://127.0.0.1:11434/api/generate' => Http::response(
            "not-json\n",
            200
        ),
    ]);

    $teacher = createAiMaterialUser('teacher');
    [, $resource] = createAiMaterialResource($teacher);
    $service = new AiMaterialExplainerService;

    $result = collect($service->streamMaterialExplanation($resource, 'Simple lesson text'))->implode('');

    expect($result)->toContain('## Simple Summary');
    expect($result)->toContain('This learning material');
});

it('normalizes ollama base urls without generate path', function () {
    Http::fake([
        'http://127.0.0.1:11434/api/generate' => Http::response(
            '{"response":"Base URL works","done":false}'."\n".'{"done":true}'."\n",
            200
        ),
    ]);

    $teacher = createAiMaterialUser('teacher');
    [, $resource] = createAiMaterialResource($teacher);
    $service = new AiMaterialExplainerService('http://127.0.0.1:11434', 'qwen3:4b');

    $result = collect($service->streamMaterialExplanation($resource, 'Simple lesson text'))->implode('');

    expect($result)->toBe('Base URL works');

    Http::assertSent(fn ($request): bool => $request->url() === 'http://127.0.0.1:11434/api/generate');
});
