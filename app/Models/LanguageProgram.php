<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LanguageProgram extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'locale_code',
        'name',
        'title',
        'description',
        'full_description',
        'flag_url',
        'certifications',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'certifications' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'program_id');
    }

    /**
     * Get the flag URL or fallback code if no flag is provided.
     *
     * @return string The flag URL or a short uppercase code (e.g., 'EN', 'FR', 'SP')
     */
    public function getFlagDisplayAttribute(): string
    {
        if ($this->flag_url) {
            return $this->flag_url;
        }

        // Generate short code from program name
        $words = explode(' ', $this->name);
        $code = '';

        foreach ($words as $word) {
            if ($word) {
                $code .= strtoupper($word[0]);
            }
        }

        return $code ?: strtoupper(substr($this->name, 0, 2));
    }
}
