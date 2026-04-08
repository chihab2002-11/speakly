<?php

namespace App\Models;

use Database\Factories\TeacherResourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherResource extends Model
{
    /** @use HasFactory<TeacherResourceFactory> */
    use HasFactory;

    public const CATEGORY_HOMEWORK = 'homework';

    public const CATEGORY_COURSE_MATERIALS = 'course_materials';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'teacher_id',
        'class_id',
        'category',
        'name',
        'description',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'download_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedCategories(): array
    {
        return [
            self::CATEGORY_HOMEWORK,
            self::CATEGORY_COURSE_MATERIALS,
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }
}
