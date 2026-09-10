<?php

namespace App\Services\Ai;

use App\Models\TeacherResource;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Throwable;

class ResourceTextExtractor
{
    public function __construct(private readonly Parser $parser) {}

    /**
     * Extract readable text content from a TeacherResource file.
     *
     * For PDF files, the text is parsed from the document itself.
     * For all other file types, a descriptive metadata string is returned
     * so the AI still has useful context to work with.
     */
    public function extract(TeacherResource $resource): string
    {
        $filePath = (string) $resource->file_path;
        $mimeType = (string) ($resource->mime_type ?? '');

        if ($mimeType === 'application/pdf' || str_ends_with(strtolower($filePath), '.pdf')) {
            return $this->extractFromPdf($resource, $filePath);
        }

        return $this->buildMetadataFallback($resource);
    }

    private function extractFromPdf(TeacherResource $resource, string $filePath): string
    {
        try {
            $absolutePath = Storage::path($filePath);
            $pdf = $this->parser->parseFile($absolutePath);
            $text = trim($pdf->getText());

            if ($text !== '') {
                return "--- PDF Document Content ---\n{$text}";
            }
        } catch (Throwable) {
            // Fall through to metadata fallback if parsing fails
        }

        return $this->buildMetadataFallback($resource);
    }

    /**
     * Build a structured text summary from the resource's metadata
     * when direct text extraction is not possible.
     */
    private function buildMetadataFallback(TeacherResource $resource): string
    {
        $lines = [
            '--- Resource Metadata ---',
            "Title: {$resource->name}",
            "Category: {$resource->category}",
        ];

        if (! empty($resource->description)) {
            $lines[] = "Description: {$resource->description}";
        }

        if ($resource->deadline) {
            $lines[] = "Deadline: {$resource->deadline->toDateString()}";
        }

        if (! empty($resource->original_filename)) {
            $lines[] = "Filename: {$resource->original_filename}";
        }

        return implode("\n", $lines);
    }
}
