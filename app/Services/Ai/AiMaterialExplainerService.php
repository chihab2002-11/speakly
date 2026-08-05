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
                    'options' => [
                        'temperature' => 0.25,
                    ],
                ]);

            if (! $response->successful()) {
                yield "The local AI assistant is currently unavailable. Please make sure Ollama is running and the {$this->ollamaModel()} model is installed.";

                return;
            }

            yield from $this->yieldOllamaChunks($response->toPsrResponse()->getBody());
        } catch (ConnectionException) {
            yield "The local AI assistant could not connect to Ollama at {$this->ollamaEndpoint()}. Please start Ollama and try again.";
        } catch (Throwable) {
            yield 'The local AI assistant could not explain this material right now. Please try again in a moment.';
        }
    }

    private function ollamaEndpoint(): string
    {
        return $this->endpoint !== ''
            ? $this->endpoint
            : (string) config('services.ollama.url', 'http://localhost:11434/api/generate');
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
        $safeText = Str::limit($textContent, 12000, "\n[Material text truncated for analysis.]");

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
}
