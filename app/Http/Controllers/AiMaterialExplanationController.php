<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\TeacherResource;
use App\Models\User;
use App\Services\Ai\AiProviderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiMaterialExplanationController extends Controller
{
    public function __invoke(Request $request, TeacherResource $resource, AiProviderInterface $aiProvider): StreamedResponse|JsonResponse
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $student = $request->user();

        $this->ensureStudentCanAccessResource($student, $resource);

        $textContent = $this->extractTextContent($resource);

        if ($textContent === '') {
            $textContent = implode("\n", array_filter([
                "Title: {$resource->name}",
                'Category: '.str_replace('_', ' ', (string) $resource->category),
                $resource->original_filename ? "File: {$resource->original_filename}" : null,
                $resource->description ? "Teacher Notes: {$resource->description}" : null,
                'Note: Material contains no extracted body text. Provide study summary from metadata.',
            ]));
        }

        return response()->stream(function () use ($aiProvider, $resource, $textContent): void {
            @set_time_limit(0);

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            foreach ($aiProvider->streamMaterialExplanation($resource, $textContent) as $chunk) {
                @set_time_limit(0);
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
            $extension = strtolower(pathinfo((string) ($resource->original_filename ?: $resource->file_path), PATHINFO_EXTENSION));
            $mimeType = strtolower((string) $resource->mime_type);

            // Handle PDF files using smalot/pdfparser
            if ($extension === 'pdf' || $mimeType === 'application/pdf') {
                $pdfText = '';

                try {
                    @ini_set('memory_limit', '512M');
                    $parser = new Parser;
                    $pdf = $parser->parseFile($absolutePath);
                    $pdfText = $pdf->getText();

                    if (! empty(trim($pdfText))) {
                        $parts[] = "--- PDF Document Content ---\n".trim($pdfText);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Failed to parse PDF resource ID {$resource->id}: ".$e->getMessage());
                }

                if (trim($pdfText) === '') {
                    $plainText = $this->readTextLikeFileContents($absolutePath, $extension, $mimeType);

                    if ($plainText !== '') {
                        $parts[] = "--- PDF Document Content ---\n".$plainText;
                    }
                }
            }
            // Handle plain text files
            elseif (str_starts_with($mimeType, 'text/') || in_array($extension, ['txt', 'md', 'csv', 'json', 'html', 'htm', 'xml'], true)) {
                $parts[] = Storage::disk('public')->get($resource->file_path);
            }
        }

        $extracted = trim(implode("\n\n", $parts));

        if ($extracted !== '') {
            return $extracted;
        }

        return implode("\n", array_filter([
            "Resource Title: {$resource->name}",
            'Category: '.str_replace('_', ' ', (string) $resource->category),
            $resource->original_filename ? "Filename: {$resource->original_filename}" : null,
            $resource->description ? "Teacher Notes: {$resource->description}" : null,
            'Note: No text content could be extracted directly from this file (e.g. empty or scanned media document). Provide learning guidance based on resource details.',
        ]));
    }

    private function isTextLikeResource(TeacherResource $resource): bool
    {
        $extension = strtolower(pathinfo($resource->original_filename, PATHINFO_EXTENSION));
        $mimeType = strtolower((string) $resource->mime_type);

        return str_starts_with($mimeType, 'text/')
            || $mimeType === 'application/pdf'
            || in_array($extension, ['txt', 'md', 'csv', 'json', 'pdf'], true);
    }

    private function readTextLikeFileContents(string $absolutePath, string $extension, string $mimeType): string
    {
        try {
            $contents = file_get_contents($absolutePath);
        } catch (\Throwable) {
            return '';
        }

        if ($contents === false || $contents === '') {
            return '';
        }

        if (str_starts_with($mimeType, 'text/')) {
            return trim($contents);
        }

        if (str_starts_with($contents, '%PDF-')) {
            return '';
        }

        if (str_contains($contents, "\0")) {
            return '';
        }

        if (! preg_match('/[A-Za-z0-9]/', $contents)) {
            return '';
        }

        return trim($contents);
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
