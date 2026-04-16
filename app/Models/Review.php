<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'student_name',
        'student_group',
        'review_text',
        'profile_picture_url',
        'rating_score',
        'likes_count',
        'dislikes_count',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'rating_score' => 'float',
            'uploaded_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
