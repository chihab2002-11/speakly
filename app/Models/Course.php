<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'price', 'description', 'program_id'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('price', '>', 0);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(CourseClass::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LanguageProgram::class, 'program_id');
    }

    public function studentTuitions(): HasMany
    {
        return $this->hasMany(StudentTuition::class);
    }
}
