<?php

namespace App\Jobs;

use App\Models\ResourceAiAnalysis;
use App\Models\TeacherResource;
use App\Services\Ai\AiProviderInterface;
use App\Services\Ai\ResourceTextExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateResourceAiExplanation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Give the job plenty of time to wait for Ollama (e.g., 5 minutes)
    public $timeout = 300;

    public function __construct(
        public TeacherResource $resource
    ) {}

    public function handle(
        ResourceTextExtractor $extractor,
        AiProviderInterface $aiProvider
    ): void {
        // 1. Get or create the analysis record and mark it as processing
        $analysis = ResourceAiAnalysis::firstOrCreate(
            ['teacher_resource_id' => $this->resource->id],
            ['status' => 'pending']
        );

        // If it's already completed, we don't need to process it again
        if ($analysis->status === 'completed' && !empty($analysis->content)) {
            return;
        }

        $analysis->update(['status' => 'processing', 'error_message' => null]);

        try {
            // 2. Extract text from the PDF
            $textContent = $extractor->extract($this->resource);

            // 3. Call Ollama using your existing provider
            $explanationStream = $aiProvider->streamMaterialExplanation($this->resource, $textContent);

            // 4. Capture the stream into a single string
            $finalContent = '';
            foreach ($explanationStream as $chunk) {
                $finalContent .= $chunk;
            }

            // 5. Save the result and mark as completed
            $analysis->update([
                'status' => 'completed',
                'content' => trim($finalContent),
            ]);

        } catch (Throwable $e) {
            Log::error("AI Explanation Job failed for Resource {$this->resource->id}: " . $e->getMessage());
            
            // Mark as failed so we know something went wrong
            $analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            // Let the queue worker know this job failed so it can retry if configured
            throw $e;
        }
    }
}