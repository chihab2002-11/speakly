<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\TeacherResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiMaterialExplanationController extends Controller
{
    public function __invoke(Request $request, TeacherResource $resource): StreamedResponse|JsonResponse
    {
        $student = $request->user();

        $this->ensureStudentCanAccessResource($student, $resource);

        // Retrieve the background AI analysis from the database
        $analysis = $resource->aiAnalysis;

        return response()->stream(function () use ($analysis): void {
            
            // Keep the stream alive for the frontend UI, but just send static DB text
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            if (!$analysis || in_array($analysis->status, ['pending', 'processing'])) {
                echo "> *AI explanation is currently being prepared. Please check back in a few moments.*\n";
                flush();
                return;
            }

            if ($analysis->status === 'failed') {
                echo "> *Note: The AI failed to process this document. The administrator has been notified.*\n";
                flush();
                return;
            }

            // The AI job finished! Stream the saved result.
            echo $analysis->content;
            flush();

        }, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function ensureStudentCanAccessResource(User $student, TeacherResource $resource): void
    {
        $classAccess = $this->resolveAccessibleClassIds($student)
            ->contains((int) $resource->class_id);

        $notificationAccess = $this->resolveNotifiedResourceIds($student)
            ->contains((int) $resource->id);

        abort_unless($classAccess || $notificationAccess, 403);
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