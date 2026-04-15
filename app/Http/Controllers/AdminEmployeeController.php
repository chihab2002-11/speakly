<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminEmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $secretaries = User::query()
            ->role('secretary')
            ->whereNotNull('approved_at')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'created_at']);

        $teachers = User::query()
            ->role('teacher')
            ->whereNotNull('approved_at')
            ->with([
                'taughtClasses:id,teacher_id,course_id',
                'taughtClasses.course:id,name',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('preferred_language', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'preferred_language', 'bio', 'created_at']);

        $teacherLanguageMap = [];
        $allLanguages = collect();

        foreach ($teachers as $teacher) {
            $tags = $this->teacherLanguageTags($teacher);
            $teacherLanguageMap[$teacher->id] = $tags;
            $allLanguages = $allLanguages->merge($tags);
        }

        $activeSecretaries = $secretaries->count();
        $activeTeachers = $teachers->count();

        return view('admin.employees', [
            'search' => $search,
            'secretaries' => $secretaries,
            'teachers' => $teachers,
            'teacherLanguageMap' => $teacherLanguageMap,
            'languageOptions' => ['english', 'french', 'spanish', 'german', 'arabic', 'italian', 'mandarin', 'turkish'],
            'totalActiveStaff' => $activeSecretaries + $activeTeachers,
            'activeSecretaries' => $activeSecretaries,
            'activeTeachers' => $activeTeachers,
            'activeLanguages' => $allLanguages->unique()->count(),
        ]);
    }

    public function storeSecretary(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $secretary = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'requested_role' => 'secretary',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ]);

        $secretary->syncRoles(['secretary']);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Secretary account created successfully.');
    }

    public function updateSecretary(Request $request, User $secretary): RedirectResponse
    {
        $this->ensureStaffRole($secretary, 'secretary');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($secretary->id)],
            'phone' => ['nullable', 'string', 'max:25'],
        ]);

        $secretary->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'requested_role' => 'secretary',
        ]);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Secretary account updated successfully.');
    }

    public function destroySecretary(Request $request, User $secretary): RedirectResponse
    {
        $this->ensureStaffRole($secretary, 'secretary');

        if ((int) $request->user()->id === (int) $secretary->id) {
            return redirect()
                ->route('admin.employees.index')
                ->withErrors(['secretary' => 'You cannot delete your own account.']);
        }

        if (Schema::hasTable('tuition_payments') && $secretary->tuitionPaymentsRecorded()->exists()) {
            return redirect()
                ->route('admin.employees.index')
                ->withErrors(['secretary' => 'Cannot delete secretary who recorded payment history.']);
        }

        if (Schema::hasTable('messages')) {
            $hasMessages = Message::query()
                ->where('sender_id', $secretary->id)
                ->orWhere('receiver_id', $secretary->id)
                ->exists();

            if ($hasMessages) {
                return redirect()
                    ->route('admin.employees.index')
                    ->withErrors(['secretary' => 'Cannot delete secretary with message history.']);
            }
        }

        $secretary->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Secretary account deleted successfully.');
    }

    public function storeTeacher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'preferred_language' => ['nullable', Rule::in($this->allowedLanguages())],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $teacher = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'preferred_language' => $validated['preferred_language'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'password' => Hash::make($validated['password']),
            'requested_role' => 'teacher',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ]);

        $teacher->syncRoles(['teacher']);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Teacher account created successfully.');
    }

    public function updateTeacher(Request $request, User $teacher): RedirectResponse
    {
        $this->ensureStaffRole($teacher, 'teacher');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'preferred_language' => ['nullable', Rule::in($this->allowedLanguages())],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $teacher->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'preferred_language' => $validated['preferred_language'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'requested_role' => 'teacher',
        ]);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Teacher account updated successfully.');
    }

    public function assignTeacherLanguage(Request $request, User $teacher): RedirectResponse
    {
        $this->ensureStaffRole($teacher, 'teacher');

        $validated = $request->validate([
            'preferred_language' => ['required', Rule::in($this->allowedLanguages())],
        ]);

        $teacher->update([
            'preferred_language' => $validated['preferred_language'],
        ]);

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Teacher language assignment updated successfully.');
    }

    public function destroyTeacher(Request $request, User $teacher): RedirectResponse
    {
        $this->ensureStaffRole($teacher, 'teacher');

        if ((int) $request->user()->id === (int) $teacher->id) {
            return redirect()
                ->route('admin.employees.index')
                ->withErrors(['teacher' => 'You cannot delete your own account.']);
        }

        if (CourseClass::query()->where('teacher_id', $teacher->id)->exists()) {
            return redirect()
                ->route('admin.employees.index')
                ->withErrors(['teacher' => 'Cannot delete teacher assigned to groups.']);
        }

        if ($teacher->teacherResources()->exists()) {
            return redirect()
                ->route('admin.employees.index')
                ->withErrors(['teacher' => 'Cannot delete teacher with uploaded resources.']);
        }

        if (Schema::hasTable('messages')) {
            $hasMessages = Message::query()
                ->where('sender_id', $teacher->id)
                ->orWhere('receiver_id', $teacher->id)
                ->exists();

            if ($hasMessages) {
                return redirect()
                    ->route('admin.employees.index')
                    ->withErrors(['teacher' => 'Cannot delete teacher with message history.']);
            }
        }

        $teacher->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Teacher account deleted successfully.');
    }

    /**
     * @return list<string>
     */
    private function teacherLanguageTags(User $teacher): array
    {
        $tags = collect();

        if ($teacher->preferred_language !== null && $teacher->preferred_language !== '') {
            $tags->push(strtoupper((string) $teacher->preferred_language));
        }

        foreach ($teacher->taughtClasses as $group) {
            $courseName = (string) ($group->course?->name ?? '');
            $language = $this->extractLanguageName($courseName);

            if ($language !== null) {
                $tags->push(strtoupper($language));
            }
        }

        return $tags
            ->filter()
            ->map(fn ($tag): string => trim((string) $tag))
            ->unique()
            ->take(4)
            ->values()
            ->all();
    }

    private function extractLanguageName(string $courseName): ?string
    {
        if ($courseName === '') {
            return null;
        }

        $firstWord = (string) preg_replace('/[^A-Za-z]/', '', (string) explode(' ', trim($courseName))[0]);

        return $firstWord !== '' ? $firstWord : null;
    }

    /**
     * @return list<string>
     */
    private function allowedLanguages(): array
    {
        return ['english', 'french', 'spanish', 'german', 'arabic', 'italian', 'mandarin', 'turkish'];
    }

    private function ensureStaffRole(User $user, string $role): void
    {
        $user->loadMissing('roles');
        abort_unless($user->hasRole($role), 404);
    }
}
