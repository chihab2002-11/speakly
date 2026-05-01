<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'preferred_language',
        'bio',
        'password',
        'requested_role',
        'date_of_birth',
        'parent_id',
        'requested_course_id',
        'registration_document_type',
        'registration_document_original_filename',
        'registration_document_path',
        'registration_document_mime_type',
        'registration_document_size',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'registration_document_size' => 'integer',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function enrolledClasses(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'class_student', 'user_id', 'class_id')
            ->withTimestamps()
            ->withPivot('enrolled_at');
    }

    public function taughtClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'teacher_id');
    }

    public function teacherResources(): HasMany
    {
        return $this->hasMany(TeacherResource::class, 'teacher_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function requestedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'requested_course_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    public function studentCards(): HasMany
    {
        return $this->hasMany(StudentCard::class);
    }

    public function tuitionPaymentsAsStudent(): HasMany
    {
        return $this->hasMany(TuitionPayment::class, 'student_id');
    }

    public function tuitionPaymentsAsParent(): HasMany
    {
        return $this->hasMany(TuitionPayment::class, 'parent_id');
    }

    public function tuitionPaymentsRecorded(): HasMany
    {
        return $this->hasMany(TuitionPayment::class, 'recorded_by');
    }

    public function employeePayment(): HasOne
    {
        return $this->hasOne(EmployeePayment::class, 'employee_id');
    }

    public function employeePaymentsRecorded(): HasMany
    {
        return $this->hasMany(EmployeePayment::class, 'recorded_by');
    }

    public function studentTuition(): HasOne
    {
        return $this->hasOne(StudentTuition::class, 'student_id');
    }

    public function scholarshipActivationsAsParent(): HasMany
    {
        return $this->hasMany(ScholarshipActivation::class, 'parent_id');
    }

    public function scholarshipActivationsAsStudent(): HasMany
    {
        return $this->hasMany(ScholarshipActivation::class, 'student_id');
    }

    public function latestStudentCard(): ?StudentCard
    {
        return $this->studentCards()->latest('valid_to')->first();
    }

    public function currentStudentCard(): ?StudentCard
    {
        return $this->studentCards()
            ->where('status', 'active')
            ->whereDate('valid_from', '<=', now())
            ->whereDate('valid_to', '>=', now())
            ->latest('valid_to')
            ->first();
    }

    public function getStudentAgeAttribute(): ?int
    {
        if (! $this->date_of_birth) {
            return null;
        }

        return Carbon::parse($this->date_of_birth)->age;
    }

    public function isUnderageStudent(): bool
    {
        return $this->hasRole('student')
            && $this->student_age !== null
            && $this->student_age < 18;
    }

    public function canViewStudentFinancialInformation(): bool
    {
        return $this->hasRole('student') && ! $this->isUnderageStudent();
    }

    public function requiredRegistrationDocumentType(): ?string
    {
        return match ($this->requested_role) {
            'student' => 'birth_certificate',
            'teacher', 'secretary' => 'cv',
            default => null,
        };
    }

    public function requiredRegistrationDocumentLabel(): ?string
    {
        return match ($this->requiredRegistrationDocumentType()) {
            'birth_certificate' => 'Birth Certificate',
            'cv' => 'C.V',
            default => null,
        };
    }
}
