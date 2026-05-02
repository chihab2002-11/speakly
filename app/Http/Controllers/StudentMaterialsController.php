<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\TeacherResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentMaterialsController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();

        $classIds = $this->resolveAccessibleClassIds($student);
        $notifiedResourceIds = $this->resolveNotifiedResourceIds($student);

        $resources = collect();

        if ($classIds->isNotEmpty() || $notifiedResourceIds->isNotEmpty()) {
            $resources = TeacherResource::query()
                ->where(function ($query) use ($classIds, $notifiedResourceIds): void {
                    if ($classIds->isNotEmpty()) {
                        $query->whereIn('class_id', $classIds);
                    }

                    if ($notifiedResourceIds->isNotEmpty()) {
                        $method = $classIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                        $query->{$method}('id', $notifiedResourceIds);
                    }
                })
                ->with(['teacher:id,name', 'courseClass.course:id,name'])
                ->orderByDesc('created_at')
                ->get();
        }

        $materials = $resources
            ->map(function (TeacherResource $resource): array {
                $extension = strtolower(pathinfo($resource->original_filename, PATHINFO_EXTENSION));
                $type = in_array($extension, ['pdf', 'doc', 'docx', 'zip'], true) ? $extension : 'pdf';
                $isHomework = $resource->category === TeacherResource::CATEGORY_HOMEWORK;

                return [
                    'id' => (int) $resource->id,
                    'resourceName' => (string) $resource->name,
                    'className' => (string) ($resource->courseClass?->course?->name ?? ('Class #'.$resource->class_id)),
                    'type' => $type,
                    'category' => $isHomework ? 'homework' : 'course',
                    'deadline' => $isHomework ? $resource->deadline?->format('Y-m-d') : null,
                    'teacher' => (string) ($resource->teacher?->name ?? 'Teacher'),
                    'sizeMb' => round(((int) $resource->file_size) / (1024 * 1024), 2),
                    'description' => (string) ($resource->description ?? ''),
                    'uploadedAt' => $resource->created_at?->toIso8601String(),
                    'isNew' => (bool) ($resource->created_at?->gte(now()->subDay()) ?? false),
                    'downloadUrl' => route('student.materials.download', ['resource' => $resource->id]),
                    'printUrl' => route('student.materials.print', ['resource' => $resource->id]),
                ];
            })
            ->values()
            ->all();

        return view('student.materials', [
            'user' => $student,
            'materials' => $materials,
        ]);
    }

    public function download(Request $request, TeacherResource $resource): StreamedResponse
    {
        $this->ensureStudentCanAccessResource($request->user(), $resource);

        abort_unless(Storage::disk('public')->exists($resource->file_path), 404);

        $resource->increment('download_count');

        return response()->streamDownload(function () use ($resource): void {
            echo Storage::disk('public')->get($resource->file_path);
        }, $resource->original_filename);
    }

    public function print(Request $request, TeacherResource $resource): StreamedResponse
    {
        $this->ensureStudentCanAccessResource($request->user(), $resource);

        abort_unless(Storage::disk('public')->exists($resource->file_path), 404);

        $resource->increment('download_count');

        return response()->stream(function () use ($resource): void {
            echo Storage::disk('public')->get($resource->file_path);
        }, 200, [
            'Content-Type' => (string) ($resource->mime_type ?: 'application/octet-stream'),
            'Content-Disposition' => 'inline; filename="'.addslashes($resource->original_filename).'"',
        ]);
    }

    private function ensureStudentCanAccessResource(User $student, TeacherResource $resource): void
    {
        $classAccess = $this->resolveAccessibleClassIds($student)
            ->contains((int) $resource->class_id);

        $notificationAccess = $this->resolveNotifiedResourceIds($student)
            ->contains((int) $resource->id);

        $hasAccess = $classAccess || $notificationAccess;

        abort_unless($hasAccess, 403);
    }

    /**
     * @return Collection<int, int>
     */
    private function resolveAccessibleClassIds(User $student): Collection
    {
        $enrolledClassIds = $student->enrolledClasses()->pluck('classes.id');

        $attendanceClassIds = AttendanceRecord::query()
            ->where('student_id', $student->id)
            ->pluck('class_id');

        return $enrolledClassIds
            ->merge($attendanceClassIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, int>
     */
    private function resolveNotifiedResourceIds(User $student): Collection
    {
        return $student->notifications()
            ->latest()
            ->limit(300)
            ->get()
            ->pluck('data.resource_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();
    }
}
