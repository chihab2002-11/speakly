<?php

namespace App\Http\Controllers;

use Smalot\PdfParser\Parser;
use App\Models\AttendanceRecord;
use App\Models\TeacherResource;
use App\Models\User;
use App\Services\Ai\AiProviderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiMaterialExplanationController extends Controller
{
    public function __invoke(Request $request, TeacherResource $resource, AiProviderInterface $aiProvider): StreamedResponse|JsonResponse
    {
        $student = $request->user();

        $this->ensureStudentCanAccessResource($student, $resource);

        $textContent = $this->extractTextContent($resource);

        if ($textContent === '') {
            return response()->json([
                'message' => 'This material does not contain readable text yet. Add a description or upload a text-based resource.',
            ], 422);
        }

        return response()->stream(function () use ($aiProvider, $resource, $textContent): void {
            foreach ($aiProvider->streamMaterialExplanation($resource, $textContent) as $chunk) {
                echo $chunk;

                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();
            }
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

    private function extractTextContent(TeacherResource $resource): string
    {
        $parts = [];
        $description = trim((string) ($resource->description ?? ''));

        if ($description !== '') {
            $parts[] = $description;
        }

        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            $absolutePath = Storage::disk('public')->path($resource->file_path);
            $extension = strtolower(pathinfo($resource->original_filename, PATHINFO_EXTENSION));
            $mimeType = strtolower((string) $resource->mime_type);

            // Handle PDF files using smalot/pdfparser
            if ($extension === 'pdf' || $mimeType === 'application/pdf') {
                try {
                    $parser = new Parser();
                    $pdf = $parser->parseFile($absolutePath);
                    $pdfText = $pdf->getText();
                    
                    if (!empty(trim($pdfText))) {
                        $parts[] = "--- PDF Document Content ---\n" . trim($pdfText);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to parse PDF resource ID {$resource->id}: " . $e->getMessage());
                }
            } 
            // Handle plain text files
            elseif (str_starts_with($mimeType, 'text/') || in_array($extension, ['txt', 'md', 'csv', 'json'], true)) {
                $parts[] = Storage::disk('public')->get($resource->file_path);
            }
        }

        return trim(implode("\n\n", $parts));
    }

    private function isTextLikeResource(TeacherResource $resource): bool
    {
        $extension = strtolower(pathinfo($resource->original_filename, PATHINFO_EXTENSION));
        $mimeType = strtolower((string) $resource->mime_type);

        return str_starts_with($mimeType, 'text/')
            || $mimeType === 'application/pdf'
            || in_array($extension, ['txt', 'md', 'csv', 'json', 'pdf'], true);
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