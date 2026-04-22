<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'student_id',
        'offer_key',
        'discount_percent',
        'activated_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'integer',
            'activated_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
