<?php

namespace App\Http\Controllers;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentSettingsController extends Controller
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public function edit(Request $request): View
    {
        $user = $request->user();

        $enrolledClasses = $user->enrolledClasses()
            ->with(['course:id,name,code', 'schedules:id,class_id,start_time,end_time'])
            ->get();

        $approvedAt = $user->approved_at ?? now();
        $registrationYear = (int) $approvedAt->format('Y');
        $academicYear = $registrationYear.'/'.($registrationYear + 1);

        $sessionsPerWeek = (int) $enrolledClasses->sum(fn ($class): int => $class->schedules->count());

        $hoursPerWeek = (float) $enrolledClasses->sum(function ($class): float {
            return (float) $class->schedules->sum(function ($schedule): float {
                if (! $schedule->start_time || ! $schedule->end_time) {
                    return 0;
                }

                $start = strtotime((string) $schedule->start_time);
                $end = strtotime((string) $schedule->end_time);

                if (! $start || ! $end || $end <= $start) {
                    return 0;
                }

                return ($end - $start) / 3600;
            });
        });

        $courseNames = $enrolledClasses
            ->map(fn ($class): string => (string) ($class->course?->name ?? ('Class #'.$class->id)))
            ->filter(fn (string $name): bool => $name !== '')
            ->unique()
            ->values()
            ->all();

        return view('student.settings', [
            'user' => $user,
            'studentId' => 'LUM-'.now()->format('Y').'-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
            'proficiencyLevel' => 'C1',
            'proficiencyPercent' => 84,
            'proficiencyStatus' => 'Advanced',
            'passwordLastChanged' => $user->password_changed_at
                ? $user->password_changed_at->diffForHumans()
                : 'never',
            'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
            'academicInfo' => [
                'academicYear' => $academicYear,
                'registrationYear' => (string) $registrationYear,
                'enrolledClassesCount' => (int) $enrolledClasses->count(),
                'sessionsPerWeek' => $sessionsPerWeek,
                'hoursPerWeek' => round($hoursPerWeek, 1),
                'courses' => $courseNames,
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            ...$this->profileRules($user->id),
            'phone' => ['nullable', 'string', 'max:50'],
            'preferred_language' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'preferred_language' => $validated['preferred_language'] ?: null,
            'date_of_birth' => $validated['date_of_birth'] ?: null,
            'bio' => $validated['bio'] ?: null,
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('student.settings')
            ->with('success', 'Personal details updated successfully.');
    }

    public function editPassword(Request $request): View
    {
        return view('student.password', [
            'user' => $request->user(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
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
            ->route('student.settings')
            ->with('success', 'Password updated successfully.');
    }
}
