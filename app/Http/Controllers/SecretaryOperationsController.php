<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\LanguageProgram;
use App\Models\Message;
use App\Models\TuitionPayment;
use App\Models\User;
use App\Notifications\SecretaryAnnouncementNotification;
use App\Support\TuitionFinancialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SecretaryOperationsController extends Controller
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public function __construct(
        private TuitionFinancialService $tuitionFinancialService,
        private CreateNewUser $createNewUser,
    ) {}

    public function registrations(): View
    {
        return view('secretary.registrations', [
            'pendingCount' => User::query()
                ->whereNull('approved_at')
                ->whereNull('rejected_at')
                ->whereNotNull('requested_role')
                ->count(),
            'availablePrograms' => Schema::hasTable('language_programs')
                ? LanguageProgram::query()
                    ->ordered()
                    ->where('is_active', true)
                    ->get(['id', 'name', 'code'])
                : collect(),
            'availableCourses' => Schema::hasTable('courses') && Schema::hasTable('language_programs')
                ? Course::query()
                    ->available()
                    ->whereNotNull('program_id')
                    ->whereHas('program', function ($query): void {
                        $query->where('is_active', true);
                    })
                    ->orderBy('name')
                    ->get(['id', 'program_id', 'name', 'code', 'price'])
                : collect(),
        ]);
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        $createdUser = $this->createNewUser->create($request->all());

        return redirect()
            ->route('secretary.registrations')
            ->with('success', "Registration created for {$createdUser->name}. It is now pending approval.");
    }

    public function payments(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $students = User::query()
            ->role('student')
            ->whereNotNull('approved_at')
            ->withCount('enrolledClasses')
            ->with($this->studentPaymentRelations())
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get();

        $payments = $this->tuitionFinancialService->buildSecretaryRows($students);

        if (in_array($status, ['paid', 'pending'], true)) {
            $payments = $payments
                ->where('status', $status)
                ->values();
        }

        return view('secretary.payments', [
            'payments' => $payments,
            'totalStudents' => $payments->count(),
            'totalEstimatedRevenue' => (int) $payments->sum('net_due'),
            'totalCollected' => (int) $payments->sum('amount_paid'),
            'totalOutstanding' => (int) $payments->sum('balance'),
            'pendingCount' => (int) $payments->where('status', 'pending')->count(),
            'paidCount' => (int) $payments->where('status', 'paid')->count(),
            'status' => $status,
            'search' => $search,
            'methods' => [
                'cash' => 'Cash',
                'bank_transfer' => 'Bank Transfer',
                'card' => 'Card',
                'online' => 'Online',
            ],
            'paymentsEnabled' => $this->tuitionFinancialService->canRecordPayments(),
            'coursePricingEnabled' => $this->tuitionFinancialService->hasCoursePricing(),
        ]);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('tuition_payments')) {
            return redirect()
                ->route('secretary.payments')
                ->with('error', 'Payments table is missing. Run migrations first (php artisan migrate).');
        }

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:100000000'],
            'paid_on' => ['nullable', 'date'],
            'method' => ['required', Rule::in(['cash', 'bank_transfer', 'card', 'online'])],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $student = User::query()
            ->where('id', (int) $validated['student_id'])
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'student');
            })
            ->firstOrFail();

        TuitionPayment::query()->create([
            'student_id' => $student->id,
            'parent_id' => $student->parent_id,
            'recorded_by' => $request->user()->id,
            'amount' => (int) $validated['amount'],
            'paid_on' => $validated['paid_on'] ?? now()->toDateString(),
            'method' => $validated['method'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('secretary.payments')
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * @return array<int|string, mixed>
     */
    private function studentPaymentRelations(): array
    {
        $relations = ['enrolledClasses.course'];

        if (Schema::hasTable('student_tuitions')) {
            $relations[] = 'studentTuition.course';
        }

        if (Schema::hasTable('tuition_payments')) {
            $relations['tuitionPaymentsAsStudent'] = fn ($query) => $query->orderByDesc('paid_on')->orderByDesc('id');
        }

        return $relations;
    }

    public function groups(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $day = strtolower((string) $request->query('day', ''));
        $teacherId = (string) $request->query('teacher_id', '');
        $courseId = (string) $request->query('course_id', '');

        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $day = in_array($day, $validDays, true) ? $day : '';

        $groupsQuery = CourseClass::query()
            ->with([
                'course:id,name,code',
                'teacher:id,name',
                'schedules:id,class_id,day_of_week,start_time,end_time,room_id',
                'schedules.room:id,name',
            ])
            ->withCount(['students', 'schedules'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('id', $search)
                        ->orWhereHas('course', function ($courseQuery) use ($search): void {
                            $courseQuery
                                ->where('name', 'like', '%'.$search.'%')
                                ->orWhere('code', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('teacher', function ($teacherQuery) use ($search): void {
                            $teacherQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($teacherId !== '' && ctype_digit($teacherId), function ($query) use ($teacherId): void {
                $query->where('teacher_id', (int) $teacherId);
            })
            ->when($courseId !== '' && ctype_digit($courseId), function ($query) use ($courseId): void {
                $query->where('course_id', (int) $courseId);
            })
            ->when($day !== '', function ($query) use ($day): void {
                $query->whereHas('schedules', function ($scheduleQuery) use ($day): void {
                    $scheduleQuery->where('day_of_week', $day);
                });
            });

        $groups = $groupsQuery
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $allGroupsCount = CourseClass::query()->count();
        $allActiveToday = CourseClass::query()
            ->whereHas('schedules', function ($query): void {
                $query->where('day_of_week', strtolower(now()->englishDayOfWeek));
            })
            ->count();
        $allAssignedStudents = (int) CourseClass::query()
            ->withCount('students')
            ->get()
            ->sum('students_count');
        $allOpenSlots = (int) CourseClass::query()
            ->get(['capacity'])
            ->sum('capacity') - $allAssignedStudents;

        return view('secretary.groups', [
            'groups' => $groups,
            'totalGroups' => $groups->total(),
            'totalAssignedStudents' => (int) $groups->getCollection()->sum('students_count'),
            'groupsActiveToday' => CourseClass::query()
                ->whereHas('schedules', function ($query): void {
                    $query->where('day_of_week', strtolower(now()->englishDayOfWeek));
                })
                ->count(),
            'teachers' => User::query()->role('teacher')->orderBy('name')->get(['id', 'name']),
            'courses' => Course::query()->orderBy('name')->get(['id', 'name', 'code']),
            'students' => User::query()
                ->role('student')
                ->whereNotNull('approved_at')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'search' => $search,
            'teacherId' => $teacherId,
            'courseId' => $courseId,
            'day' => $day,
            'statsTotalGroups' => $allGroupsCount,
            'statsActiveToday' => $allActiveToday,
            'statsAssignedStudents' => $allAssignedStudents,
            'statsOpenSlots' => max($allOpenSlots, 0),
        ]);
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        CourseClass::query()->create([
            'course_id' => (int) $validated['course_id'],
            'teacher_id' => isset($validated['teacher_id']) && $validated['teacher_id'] !== ''
                ? (int) $validated['teacher_id']
                : null,
            'capacity' => (int) $validated['capacity'],
        ]);

        return redirect()
            ->route('secretary.groups')
            ->with('success', 'Group created successfully.');
    }

    public function enrollStudent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $group = CourseClass::query()->withCount('students')->findOrFail((int) $validated['class_id']);

        $student = User::query()
            ->where('id', (int) $validated['student_id'])
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'student');
            })
            ->firstOrFail();

        $alreadyEnrolled = $group->students()->where('users.id', $student->id)->exists();

        if (! $alreadyEnrolled && $group->students_count >= $group->capacity) {
            return redirect()
                ->route('secretary.groups')
                ->withErrors(['class_id' => 'This group is already full.'])
                ->withInput();
        }

        $group->students()->syncWithoutDetaching([
            $student->id => ['enrolled_at' => now()],
        ]);

        return redirect()
            ->route('secretary.groups')
            ->with('success', $alreadyEnrolled
                ? 'Student is already enrolled in this group.'
                : 'Student enrolled in group successfully.');
    }

    public function updateGroup(Request $request, CourseClass $group): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $currentStudentsCount = $group->students()->count();
        $newCapacity = (int) $validated['capacity'];

        if ($newCapacity < $currentStudentsCount) {
            return redirect()
                ->route('secretary.groups')
                ->withErrors([
                    'capacity' => "Capacity cannot be lower than current enrolled students ({$currentStudentsCount}).",
                ]);
        }

        $group->update([
            'course_id' => (int) $validated['course_id'],
            'teacher_id' => isset($validated['teacher_id']) && $validated['teacher_id'] !== ''
                ? (int) $validated['teacher_id']
                : null,
            'capacity' => $newCapacity,
        ]);

        return redirect()
            ->route('secretary.groups')
            ->with('success', 'Group updated successfully.');
    }

    public function destroyGroup(CourseClass $group): RedirectResponse
    {
        $hasSchedules = $group->schedules()->exists();

        if ($hasSchedules) {
            return redirect()
                ->route('secretary.groups')
                ->withErrors([
                    'group' => 'Cannot delete this group because it has schedule slots. Remove schedule entries first.',
                ]);
        }

        $group->students()->detach();
        $group->delete();

        return redirect()
            ->route('secretary.groups')
            ->with('success', 'Group deleted successfully.');
    }

    public function accounts(Request $request): View
    {
        $role = strtolower((string) $request->query('role', 'all'));
        $status = strtolower((string) $request->query('status', 'all'));
        $search = trim((string) $request->query('search', ''));

        $allowedRoles = ['all', 'student', 'parent', 'teacher'];
        $allowedStatus = ['all', 'approved', 'pending', 'rejected'];
        $role = in_array($role, $allowedRoles, true) ? $role : 'all';
        $status = in_array($status, $allowedStatus, true) ? $status : 'all';

        $accountsQuery = User::query()
            ->with('roles:id,name')
            ->where(function ($query): void {
                $query
                    ->whereIn('requested_role', ['student', 'parent', 'teacher'])
                    ->orWhereHas('roles', function ($roleQuery): void {
                        $roleQuery->whereIn('name', ['student', 'parent', 'teacher']);
                    });
            })
            ->when($role !== 'all', function ($query) use ($role): void {
                $query->where(function ($innerQuery) use ($role): void {
                    $innerQuery
                        ->where('requested_role', $role)
                        ->orWhereHas('roles', function ($roleQuery) use ($role): void {
                            $roleQuery->where('name', $role);
                        });
                });
            })
            ->when($status === 'approved', fn ($query) => $query->whereNotNull('approved_at'))
            ->when($status === 'pending', function ($query): void {
                $query
                    ->whereNull('approved_at')
                    ->whereNull('rejected_at');
            })
            ->when($status === 'rejected', fn ($query) => $query->whereNotNull('rejected_at'))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            });

        $accounts = $accountsQuery
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $baseScope = User::query()->where(function ($query): void {
            $query
                ->whereIn('requested_role', ['student', 'parent', 'teacher'])
                ->orWhereHas('roles', function ($roleQuery): void {
                    $roleQuery->whereIn('name', ['student', 'parent', 'teacher']);
                });
        });

        return view('secretary.accounts', [
            'accounts' => $accounts,
            'role' => $role,
            'status' => $status,
            'search' => $search,
            'totalManagedAccounts' => (clone $baseScope)->count(),
            'approvedAccounts' => (clone $baseScope)->whereNotNull('approved_at')->count(),
            'pendingAccounts' => (clone $baseScope)->whereNull('approved_at')->whereNull('rejected_at')->count(),
            'rejectedAccounts' => (clone $baseScope)->whereNotNull('rejected_at')->count(),
        ]);
    }

    public function updateAccount(Request $request, User $account): RedirectResponse
    {
        if (! $this->isManageableAccount($account)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($account->id)],
            'requested_role' => ['required', Rule::in(['student', 'parent', 'teacher'])],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        $account->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'requested_role' => $validated['requested_role'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'requested_course_id' => $validated['requested_role'] === 'student'
                ? $account->requested_course_id
                : null,
        ])->save();

        if ($account->approved_at !== null) {
            $account->syncRoles([$validated['requested_role']]);
        }

        if ($validated['requested_role'] !== 'student' && Schema::hasTable('student_tuitions')) {
            $account->studentTuition()->delete();
        }

        return redirect()
            ->route('secretary.accounts')
            ->with('success', 'Account updated successfully.');
    }

    public function destroyAccount(User $account): RedirectResponse
    {
        if (! $this->isManageableAccount($account)) {
            abort(404);
        }

        if ($account->hasRole('admin') || $account->hasRole('secretary')) {
            return redirect()
                ->route('secretary.accounts')
                ->withErrors(['account' => 'Admin and secretary accounts cannot be deleted from this screen.']);
        }

        if (Schema::hasTable('messages')) {
            $hasMessages = Message::query()
                ->where('sender_id', $account->id)
                ->orWhere('receiver_id', $account->id)
                ->exists();

            if ($hasMessages) {
                return redirect()
                    ->route('secretary.accounts')
                    ->withErrors(['account' => 'This account cannot be deleted because it has message history.']);
            }
        }

        $account->enrolledClasses()->detach();

        if (Schema::hasTable('student_tuitions')) {
            $account->studentTuition()->delete();
        }

        if (Schema::hasTable('tuition_payments')) {
            $account->tuitionPaymentsAsStudent()->delete();
            $account->tuitionPaymentsAsParent()->delete();
            $account->tuitionPaymentsRecorded()->delete();
        }

        $account->delete();

        return redirect()
            ->route('secretary.accounts')
            ->with('success', 'Account deleted successfully.');
    }

    private function isManageableAccount(User $account): bool
    {
        $account->loadMissing('roles:id,name');

        if (in_array((string) $account->requested_role, ['student', 'parent', 'teacher'], true)) {
            return true;
        }

        return $account->roles->pluck('name')->intersect(['student', 'parent', 'teacher'])->isNotEmpty();
    }

    public function publishNotifications(): View
    {
        return view('secretary.publish-notifications', [
            'audienceCounts' => [
                'students' => User::query()->role('student')->whereNotNull('approved_at')->count(),
                'parents' => User::query()->role('parent')->whereNotNull('approved_at')->count(),
                'teachers' => User::query()->role('teacher')->whereNotNull('approved_at')->count(),
                'admins' => User::query()->role('admin')->whereNotNull('approved_at')->count(),
                'secretaries' => User::query()->role('secretary')->whereNotNull('approved_at')->count(),
            ],
        ]);
    }

    public function sendPublishedNotification(Request $request): RedirectResponse
    {
        $issuer = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
            'audience' => ['required', 'in:all,students,parents,teachers'],
            'include_secretaries' => ['nullable', 'boolean'],
            'url' => ['nullable', 'url', 'max:255'],
        ]);

        $roleTargets = match ($validated['audience']) {
            'students' => ['student'],
            'parents' => ['parent'],
            'teachers' => ['teacher'],
            default => ['student', 'parent', 'teacher', 'admin'],
        };

        if (! empty($validated['include_secretaries'])) {
            $roleTargets[] = 'secretary';
        }

        $roleTargets = array_values(array_unique($roleTargets));

        $recipientsQuery = User::query()
            ->whereNotNull('approved_at')
            ->where('id', '!=', $issuer->id)
            ->whereHas('roles', function ($query) use ($roleTargets): void {
                $query->whereIn('name', $roleTargets);
            })
            ->orderBy('id');

        $sentCount = 0;

        $recipientsQuery->chunkById(200, function ($users) use (&$sentCount, $validated, $issuer): void {
            foreach ($users as $user) {
                $user->notify(new SecretaryAnnouncementNotification(
                    title: $validated['title'],
                    message: $validated['message'],
                    url: $validated['url'] ?? null,
                    issuerId: $issuer->id,
                    issuerName: $issuer->name,
                ));
                $sentCount++;
            }
        });

        return redirect()
            ->route('secretary.publish-notifications')
            ->with('success', "Notification published to {$sentCount} recipient(s).");
    }

    public function settings(Request $request): View
    {
        $user = $request->user();

        $managedAccounts = User::query()
            ->whereNotNull('approved_at')
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', ['student', 'parent', 'teacher']);
            })
            ->count();

        $pendingApprovals = User::query()
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereIn('requested_role', ['student', 'parent', 'teacher'])
            ->count();

        return view('secretary.settings', [
            'user' => $user,
            'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
            'passwordLastChanged' => $user->password_changed_at?->diffForHumans() ?? 'Not tracked yet',
            'managedAccounts' => $managedAccounts,
            'pendingApprovals' => $pendingApprovals,
            'yearsInRole' => max(0, (int) $user->created_at?->diffInYears(now())),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            ...$this->profileRules($user->id),
            'phone' => ['nullable', 'string', 'max:25'],
            'preferred_language' => ['nullable', Rule::in(['english', 'french', 'spanish', 'german', 'arabic'])],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('secretary.settings')
            ->with('success', 'Profile settings updated successfully.');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => $this->currentPasswordRules(),
            'password' => [...$this->passwordRules(), 'different:current_password'],
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ])->save();

        return redirect()
            ->route('secretary.settings')
            ->with('success', 'Password updated successfully.');
    }
}
