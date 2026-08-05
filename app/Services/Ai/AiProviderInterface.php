<?php

namespace App\Services\Ai;

use App\Models\TeacherResource;
use Generator;

interface AiProviderInterface
{
    /**
     * @return Generator<int, string>
     */
    public function streamMaterialExplanation(TeacherResource $resource, string $textContent): Generator;
}
