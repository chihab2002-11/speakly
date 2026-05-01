<?php

namespace App\Http\Controllers;

use App\Concerns\ProfileValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    use ProfileValidationRules;

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('admin.settings', [
            'user' => $user,
            'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
            'passwordLastChanged' => $user->password_changed_at?->diffForHumans() ?? 'Not tracked yet',
            'yearsInRole' => max(0, (int) $user->created_at?->diffInYears(now())),
        ]);
    }

    public function update(Request $request): RedirectResponse
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
            ->route('admin.settings')
            ->with('success', 'Profile settings updated successfully.');
    }
}
