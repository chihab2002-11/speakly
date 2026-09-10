<?php

namespace App\Services\Ai;

use App\Models\TeacherResource;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class AiMaterialExplainerService implements AiProviderInterface
{
    public function __construct(
        private readonly string $endpoint = '',
        private readonly string $model = '',
    ) {}

    /**
     * @return Generator<int, string>
     */
    public function streamMaterialExplanation(TeacherResource $resource, string $textContent): Generator
    {
        try {
            $response = Http::connectTimeout(3)
                ->timeout(120)
                ->acceptJson()
                ->withOptions(['stream' => true])
                ->post($this->ollamaEndpoint(), [
                    'model' => $this->ollamaModel(),
                    'prompt' => $this->buildPrompt($resource, $textContent),
                    'stream' => true,
                    'think' => false,
                    'options' => [
                        'temperature' => 0.25,
                        'num_predict' => 450,
                    ],
                ]);

            if (! $response->successful()) {
                yield from $this->fallbackExplanation($resource, $textContent, "Note: The local Ollama AI assistant is unavailable or model '{$this->ollamaModel()}' is missing.");

                return;
            }

            $chunkCount = 0;

            foreach ($this->yieldOllamaChunks($response->toPsrResponse()->getBody()) as $chunk) {
                $chunkCount++;
                yield $chunk;
            }

            if ($chunkCount === 0) {
                yield from $this->fallbackExplanation($resource, $textContent, 'Note: The local AI assistant returned no usable content. Showing study overview based on resource metadata.');

                return;
            }
        } catch (ConnectionException) {
            yield from $this->fallbackExplanation($resource, $textContent, "Note: Local AI assistant (Ollama) is offline at {$this->ollamaEndpoint()}. Showing study overview based on resource metadata.");
        } catch (Throwable) {
            yield from $this->fallbackExplanation($resource, $textContent, 'Note: Local AI stream encountered an error. Showing study overview based on resource metadata.');
        }
    }

    /**
     * @return Generator<int, string>
     */
    private function fallbackExplanation(TeacherResource $resource, string $textContent, string $notice): Generator
    {
        $resource->loadMissing(['teacher:id,name', 'courseClass.course:id,name,code']);
        $courseName = (string) ($resource->courseClass?->course?->name ?? 'Language course');
        $teacherName = (string) ($resource->teacher?->name ?? 'Teacher');
        $category = str_replace('_', ' ', (string) $resource->category);

        yield "> *{$notice}*\n\n";
        yield "## Simple Summary\n";
        yield "This learning material **{$resource->name}** was assigned by **{$teacherName}** for **{$courseName}** ({$category}).\n\n";

        if (! empty(trim((string) $resource->description))) {
            yield "**Teacher Notes:**\n".trim((string) $resource->description)."\n\n";
        }

        if (str_contains($textContent, '--- PDF Document Content ---')) {
            $pdfContent = Str::after($textContent, '--- PDF Document Content ---');
            $snippet = Str::limit(trim($pdfContent), 400);
            yield "**Document Preview Snippet:**\n".'> '.str_replace("\n", "\n> ", $snippet)."\n\n";
        }

        yield "## Key Study Focus\n";
        yield "- Review key concepts in **{$resource->name}** for your **{$courseName}** class.\n";
        yield "- Pay close attention to vocabulary, grammar rules, or exercises assigned in this resource.\n";
        if ($resource->deadline) {
            yield "- **Deadline:** Complete homework by {$resource->deadline->format('Y-m-d')}.\n";
        }
        yield "\n";

        yield "## Self-Check Questions\n";
        yield "1. What main topic or skill is covered in **{$resource->name}**?\n";
        yield "2. How does this material relate to your recent lesson with **{$teacherName}**?\n";
        yield "3. Are there any vocabulary words or exercises in this file that require extra practice?\n";
    }

    private function ollamaEndpoint(): string
    {
        if ($this->endpoint !== '') {
            return $this->normalizeOllamaEndpoint($this->endpoint);
        }

        $configuredGenerateUrl = config('services.ollama.url');
        if (is_string($configuredGenerateUrl) && trim($configuredGenerateUrl) !== '') {
            return $this->normalizeOllamaEndpoint($configuredGenerateUrl);
        }

        return $this->normalizeOllamaEndpoint((string) config('services.ollama.base_url', 'http://localhost:11434'));
    }

    private function ollamaModel(): string
    {
        return $this->model !== ''
            ? $this->model
            : (string) config('services.ollama.model', 'qwen3:4b');
    }

    private function buildPrompt(TeacherResource $resource, string $textContent): string
    {
        $resource->loadMissing(['teacher:id,name', 'courseClass.course:id,name,code']);

        $course = $resource->courseClass?->course;
        $teacherName = (string) ($resource->teacher?->name ?? 'Teacher');
        $courseName = (string) ($course?->name ?? 'Language course');
        $courseCode = (string) ($course?->code ?? 'N/A');
        $category = str_replace('_', ' ', (string) $resource->category);
        $deadline = $resource->deadline?->toDateString() ?? 'No deadline';
        $description = trim((string) ($resource->description ?? ''));
        $safeText = Str::limit($textContent, 5000, "\n[Material text truncated for analysis.]");

        return <<<PROMPT
You are Lumina Academy's AI study assistant for language learners.

Help the student understand this teacher-provided learning material. Be clear, practical, and supportive. Do not invent facts that are not in the material.

Resource:
- Title: {$resource->name}
- Course: {$courseName} ({$courseCode})
- Teacher: {$teacherName}
- Category: {$category}
- Deadline: {$deadline}
- Teacher description: {$description}

Return the answer in this exact structure:

## Simple Summary
Explain the material in simple student-friendly language.

## Key Vocabulary
List important words or phrases. For each item, provide a short meaning and one example sentence.

## Self-Check Questions
Create exactly 3 questions the student can answer to check understanding.

Material text:
{$safeText}
PROMPT;
    }

    /**
     * @return Generator<int, string>
     */
    private function yieldOllamaChunks(mixed $body): Generator
    {
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(1024);

            while (($lineEndingPosition = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $lineEndingPosition));
                $buffer = substr($buffer, $lineEndingPosition + 1);

                if ($chunk = $this->parseOllamaLine($line)) {
                    yield $chunk;
                }
            }
        }

        $line = trim($buffer);

        if ($line !== '' && ($chunk = $this->parseOllamaLine($line))) {
            yield $chunk;
        }
    }

    private function parseOllamaLine(string $line): ?string
    {
        if ($line === '') {
            return null;
        }

        $payload = json_decode($line, true);

        if (! is_array($payload)) {
            return null;
        }

        if (isset($payload['error'])) {
            return 'The local AI assistant returned an error: '.(string) $payload['error'];
        }

        $chunk = $payload['response'] ?? null;

        return is_string($chunk) && $chunk !== '' ? $chunk : null;
    }

    private function normalizeOllamaEndpoint(string $endpoint): string
    {
        $trimmedEndpoint = rtrim(trim($endpoint), '/');

        if ($trimmedEndpoint === '') {
            return 'http://127.0.0.1:11434/api/generate';
        }

        $trimmedEndpoint = str_replace('://localhost', '://127.0.0.1', $trimmedEndpoint);

        if (Str::endsWith($trimmedEndpoint, '/api/generate')) {
            return $trimmedEndpoint;
        }

        if (Str::endsWith($trimmedEndpoint, '/api')) {
            return $trimmedEndpoint.'/generate';
        }

        return $trimmedEndpoint.'/api/generate';
    }
}
