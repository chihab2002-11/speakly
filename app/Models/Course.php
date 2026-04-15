<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'price', 'description'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(CourseClass::class);
    }
}
