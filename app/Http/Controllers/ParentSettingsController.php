<?php

namespace App\Http\Controllers;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentSettingsController extends Controller
{
    use ProfileValidationRules;

    public function edit(Request $request): View
    {
        $parent = $request->user();

        $children = User::query()
            ->where('parent_id', $parent->id)
            ->whereNotNull('approved_at')
            ->whereHas('roles', fn ($query) => $query->where('name', 'student'))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'date_of_birth', 'created_at'])
            ->values()
            ->map(function (User $child, int $index): array {
                $theme = $index % 2 === 0
                    ? ['color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)']
                    : ['color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'];

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'initials' => $child->initials(),
                    'grade' => 'Student',
                    'email' => (string) ($child->email ?? ''),
                    'phone' => (string) ($child->phone ?? ''),
                    'dateOfBirth' => $child->date_of_birth ? $child->date_of_birth->format('Y-m-d') : null,
                    'memberSince' => optional($child->created_at)->format('Y-m-d'),
                    'color' => $theme['color'],
                    'textColor' => $theme['textColor'],
                ];
            })
            ->all();

        return view('parent.settings', [
            'user' => $parent,
            'children' => $children,
            'twoFactorEnabled' => $parent->two_factor_confirmed_at !== null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $parent = $request->user();

        $validated = $request->validate([
            ...$this->profileRules($parent->id),
            'phone' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $emailChanged = $validated['email'] !== $parent->email;

        $parent->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'bio' => $validated['bio'] ?: null,
        ]);

        if ($emailChanged) {
            $parent->email_verified_at = null;
        }

        $parent->save();

        return redirect()
            ->route('parent.settings')
            ->with('success', 'Settings updated successfully.');
    }
}
