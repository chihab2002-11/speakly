<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_number',
        'valid_from',
        'valid_to',
        'academic_year',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saving(function (StudentCard $card): void {
            $approvedAt = $card->user?->approved_at
                ? Carbon::parse($card->user->approved_at)
                : null;

            $sourceDate = $approvedAt ?? now();
            $registrationYear = $sourceDate->year;

            $card->valid_from = $sourceDate->copy()->toDateString();
            $card->valid_to = $sourceDate->copy()->addMonths(6)->toDateString();

            $card->academic_year = $registrationYear.'/'.($registrationYear + 1);
        });
    }

    public function isCurrentlyValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = now()->startOfDay();

        return $today->betweenIncluded($this->valid_from->startOfDay(), $this->valid_to->startOfDay());
    }

    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'suspended' || $this->status === 'inactive') {
            return $this->status;
        }

        $today = now()->startOfDay();

        if ($today->lt($this->valid_from->startOfDay())) {
            return 'inactive';
        }

        if ($today->gt($this->valid_to->startOfDay())) {
            return 'expired';
        }

        return 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereDate('valid_from', '<=', now())
            ->whereDate('valid_to', '>=', now());
    }
}
