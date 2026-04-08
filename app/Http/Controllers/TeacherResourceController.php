<?php

namespace App\Http\Controllers;

use App\Models\TeacherResource;
use App\Models\User;
use App\Notifications\TeacherResourceActionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherResourceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();
        $maxUploadKilobytes = $this->maxUploadKilobytes();
        $categoryFilter = (string) $request->query('category_id');
        $search = trim((string) $request->query('search', ''));
        $classId = $request->query('class_id');
        $fileType = strtolower(trim((string) $request->query('file_type', '')));
        $sortBy = (string) $request->query('sort_by', 'recent');

        $allowedFileTypes = ['pdf', 'doc', 'docx', 'zip'];
        $allowedSorts = ['recent', 'downloads', 'name', 'size'];

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'recent';
        }

        $classes = $teacher->taughtClasses()
            ->with('course')
            ->withCount('students')
            ->orderBy('created_at')
            ->get()
            ->map(function ($class): array {
                return [
                    'id' => $class->id,
                    'name' => $class->course?->name ?? 'Class #'.$class->id,
                    'students_count' => $class->students_count,
                ];
            })
            ->values()
            ->all();

        $resourcesQuery = TeacherResource::query()
            ->where('teacher_id', $teacher->id)
            ->with(['courseClass.course']);

        if ($classId !== null && $classId !== '') {
            $resourcesQuery->where('class_id', (int) $classId);
        }

        if (in_array($categoryFilter, TeacherResource::allowedCategories(), true)) {
            $resourcesQuery->where('category', $categoryFilter);
        }

        if ($search !== '') {
            $resourcesQuery->where('name', 'like', '%'.$search.'%');
        }

        if (in_array($fileType, $allowedFileTypes, true)) {
            $resourcesQuery->whereRaw('LOWER(original_filename) LIKE ?', ['%.'.$fileType]);
        }

        match ($sortBy) {
            'downloads' => $resourcesQuery->orderByDesc('download_count')->orderByDesc('created_at'),
            'name' => $resourcesQuery->orderBy('name')->orderByDesc('created_at'),
            'size' => $resourcesQuery->orderByDesc('file_size')->orderByDesc('created_at'),
            default => $resourcesQuery->orderByDesc('created_at'),
        };

        $resources = $resourcesQuery
            ->get();

        $resourceStats = [
            'total' => $resources->count(),
            'downloads' => (int) $resources->sum('download_count'),
            'storage' => $this->formatBytes((int) $resources->sum('file_size')),
        ];

        return view('teacher.resources', [
            'user' => $teacher,
            'classes' => $classes,
            'recentResources' => $resources->map(fn (TeacherResource $resource): array => [
                'id' => $resource->id,
                'name' => $resource->name,
                'type' => strtoupper(pathinfo($resource->original_filename, PATHINFO_EXTENSION)),
                'size' => $this->formatBytes((int) $resource->file_size),
                'uploaded_at' => $resource->created_at,
                'downloads' => $resource->download_count,
                'category' => $resource->category,
                'category_id' => $resource->category,
                'class_id' => $resource->class_id,
                'description' => (string) ($resource->description ?? ''),
            ])->all(),
            'categories' => $this->buildCategories($resources),
            'resourceStats' => $resourceStats,
            'activeFilters' => [
                'search' => $search,
                'category_id' => $categoryFilter,
                'class_id' => $classId === null ? '' : (string) $classId,
                'file_type' => $fileType,
                'sort_by' => $sortBy,
            ],
            'maxUploadSizeLabel' => $this->formatKilobytes($maxUploadKilobytes),
        ]);
    }

    public function update(Request $request, TeacherResource $resource): RedirectResponse
    {
        $teacher = $request->user();

        $this->ensureTeacherOwnsResource($teacher, $resource);

        $validated = $request->validate($this->resourceValidationRules($teacher));

        $description = trim((string) ($validated['description'] ?? ''));

        $resource->update([
            'class_id' => (int) $validated['class_id'],
            'name' => $validated['name'],
            'category' => $validated['category_id'],
            'description' => $description === '' ? null : $description,
        ]);

        return redirect()
            ->route('teacher.resources')
            ->with('success', 'Resource updated successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user();
        $maxUploadKilobytes = $this->maxUploadKilobytes();

        if (! $teacher->taughtClasses()->exists()) {
            return redirect()
                ->route('teacher.resources')
                ->withErrors(['class_id' => 'You need at least one assigned class before uploading resources.'])
                ->withInput();
        }

        $uploadedFile = $request->file('file');

        if (! $uploadedFile instanceof UploadedFile) {
            return redirect()
                ->route('teacher.resources')
                ->withErrors(['file' => 'Please choose a file to upload. If you already selected one, ensure it does not exceed '.$this->formatKilobytes($maxUploadKilobytes).'.'])
                ->withInput();
        }

        if (! $uploadedFile->isValid()) {
            return redirect()
                ->route('teacher.resources')
                ->withErrors(['file' => $this->uploadErrorMessage($uploadedFile->getError(), $maxUploadKilobytes)])
                ->withInput();
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip', 'max:'.$maxUploadKilobytes],
            ...$this->resourceValidationRules($teacher),
        ]);

        $storedPath = Storage::disk('public')->putFile('teacher-resources/'.$teacher->id, $uploadedFile);

        if (! is_string($storedPath) || $storedPath === '') {
            return redirect()
                ->route('teacher.resources')
                ->withErrors(['file' => 'File upload failed while saving to local storage. Please try again.'])
                ->withInput();
        }

        $description = trim((string) ($validated['description'] ?? ''));

        $resource = TeacherResource::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => (int) $validated['class_id'],
            'category' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $description === '' ? null : $description,
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'file_path' => $storedPath,
            'mime_type' => $uploadedFile->getClientMimeType(),
            'file_size' => (int) $uploadedFile->getSize(),
        ]);

        $resource->loadMissing('courseClass.course');

        $teacher->notify(new TeacherResourceActionNotification(
            action: 'uploaded',
            resourceName: $resource->name,
            resourceId: $resource->id,
            className: $resource->courseClass?->course?->name,
        ));

        return redirect()
            ->route('teacher.resources')
            ->with('success', 'Resource uploaded successfully.');
    }

    public function download(Request $request, TeacherResource $resource): StreamedResponse
    {
        $this->ensureTeacherOwnsResource($request->user(), $resource);

        abort_unless(Storage::disk('public')->exists($resource->file_path), 404);

        $resource->increment('download_count');

        return response()->streamDownload(function () use ($resource): void {
            echo Storage::disk('public')->get($resource->file_path);
        }, $resource->original_filename);
    }

    public function destroy(Request $request, TeacherResource $resource): RedirectResponse
    {
        $this->ensureTeacherOwnsResource($request->user(), $resource);

        $resource->loadMissing('courseClass.course');
        $resourceId = $resource->id;
        $resourceName = $resource->name;
        $className = $resource->courseClass?->course?->name;

        Storage::disk('public')->delete($resource->file_path);
        $resource->delete();

        $request->user()->notify(new TeacherResourceActionNotification(
            action: 'deleted',
            resourceName: $resourceName,
            resourceId: $resourceId,
            className: $className,
        ));

        return redirect()
            ->route('teacher.resources')
            ->with('success', 'Resource deleted successfully.');
    }

    private function ensureTeacherOwnsResource(User $teacher, TeacherResource $resource): void
    {
        abort_unless($resource->teacher_id === $teacher->id, 403);
    }

    private function maxUploadKilobytes(): int
    {
        $applicationLimit = 51200;

        $phpLimits = array_values(array_filter([
            $this->parseIniSizeToKilobytes((string) ini_get('upload_max_filesize')),
            $this->parseIniSizeToKilobytes((string) ini_get('post_max_size')),
        ], fn (int $value): bool => $value > 0));

        if ($phpLimits === []) {
            return $applicationLimit;
        }

        return min($applicationLimit, min($phpLimits));
    }

    private function parseIniSizeToKilobytes(string $iniSize): int
    {
        $normalizedSize = trim(strtolower($iniSize));

        if ($normalizedSize === '') {
            return 0;
        }

        $multiplier = 1;
        $unit = substr($normalizedSize, -1);

        if (ctype_alpha($unit)) {
            $normalizedSize = substr($normalizedSize, 0, -1);

            $multiplier = match ($unit) {
                'g' => 1024 * 1024,
                'm' => 1024,
                'k' => 1,
                default => 1,
            };
        }

        return max(0, (int) round((float) $normalizedSize * $multiplier));
    }

    private function formatKilobytes(int $kilobytes): string
    {
        if ($kilobytes >= 1024) {
            $megabytes = $kilobytes / 1024;

            return ($megabytes === floor($megabytes)
                ? (string) (int) $megabytes
                : number_format($megabytes, 1)).' MB';
        }

        return $kilobytes.' KB';
    }

    private function uploadErrorMessage(int $errorCode, int $maxUploadKilobytes): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The selected file exceeds the server upload limit of '.$this->formatKilobytes($maxUploadKilobytes).'. Please choose a smaller file.',
            UPLOAD_ERR_PARTIAL => 'The file upload was interrupted before completion. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server is missing a temporary upload directory. Please contact support.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded file to disk. Please try again.',
            UPLOAD_ERR_EXTENSION => 'The upload was stopped by a server extension. Please try again or contact support.',
            default => 'Please choose a valid file to upload.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function resourceValidationRules(User $teacher): array
    {
        return [
            'class_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->where(fn ($query) => $query->where('teacher_id', $teacher->id)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::in(TeacherResource::allowedCategories())],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return list<array{id:string,name:string,count:int,icon:string}>
     */
    private function buildCategories(Collection $resources): array
    {
        $counts = $resources
            ->groupBy('category')
            ->map(fn (Collection $group): int => $group->count());

        return [
            [
                'id' => TeacherResource::CATEGORY_HOMEWORK,
                'name' => 'Homeworks',
                'count' => $counts[TeacherResource::CATEGORY_HOMEWORK] ?? 0,
                'icon' => 'homework',
            ],
            [
                'id' => TeacherResource::CATEGORY_COURSE_MATERIALS,
                'name' => 'Course Materials',
                'count' => $counts[TeacherResource::CATEGORY_COURSE_MATERIALS] ?? 0,
                'icon' => 'course_materials',
            ],
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, 1).' '.$units[$unitIndex];
    }
}
