<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAiAnalysis extends Model
{
    protected $fillable = [
        'teacher_resource_id',
        'status',
        'content',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(TeacherResource::class, 'teacher_resource_id');
    }
}
