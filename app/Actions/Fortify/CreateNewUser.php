<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $validator = Validator::make($input, [
            ...$this->profileRules(),
            'requested_role' => ['required', 'string', 'in:student,teacher,secretary,parent'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'parent_email' => ['nullable', 'email'],
            'password' => $this->passwordRules(),
        ]);

        $validator->after(function ($validator) use ($input) {
            if (($input['requested_role'] ?? null) !== 'student') {
                return;
            }

            if (empty($input['date_of_birth'])) {
                $validator->errors()->add('date_of_birth', 'Date of birth is required for student registration.');

                return;
            }

            $age = Carbon::parse($input['date_of_birth'])->age;

            if ($age < 18) {
                $parentEmail = $input['parent_email'] ?? null;

                if (! $parentEmail) {
                    $validator->errors()->add('parent_email', 'Parent email is required for students under 18.');

                    return;
                }

                $parentUser = User::query()
                    ->where('email', $parentEmail)
                    ->whereNotNull('approved_at')
                    ->first();

                if (! $parentUser || ! $parentUser->hasRole('parent')) {
                    $validator->errors()->add('parent_email', 'Parent account must exist and be approved.');
                }
            }
        });

        $validator->validate();

        $parentId = null;

        if (($input['requested_role'] ?? null) === 'student' && ! empty($input['date_of_birth'])) {
            $age = Carbon::parse($input['date_of_birth'])->age;

            if ($age < 18 && ! empty($input['parent_email'])) {
                $parentUser = User::query()
                    ->where('email', $input['parent_email'])
                    ->whereNotNull('approved_at')
                    ->first();

                if ($parentUser && $parentUser->hasRole('parent')) {
                    $parentId = $parentUser->id;
                }
            }
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'requested_role' => $input['requested_role'],
            'date_of_birth' => $input['date_of_birth'] ?? null,
            'parent_id' => $parentId,
            'password' => $input['password'], // User model has 'hashed' cast
            // approved_at / approved_by stay null until approval
        ]);
    }
}
