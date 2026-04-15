<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'parent_id',
        'recorded_by',
        'amount',
        'paid_on',
        'method',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
