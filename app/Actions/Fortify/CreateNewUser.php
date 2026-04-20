<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $validator = Validator::make(
            $input,
            [
                ...$this->profileRules(),
                'requested_role' => ['required', 'string', 'in:student,teacher,secretary,parent'],
                'date_of_birth' => ['nullable', 'date', 'before:today'],
                'parent_email' => ['nullable', 'email'],
                'course_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('courses', 'id')->where(fn ($query) => $query->where('price', '>', 0)),
                ],
                'password' => $this->passwordRules(),
            ],
            [
                'course_id.exists' => 'Selected course is not available for registration.',
            ],
        );

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

            if (empty($input['course_id'])) {
                $validator->errors()->add('course_id', 'Please select a course for student registration.');

                return;
            }

            if (! Course::query()->available()->whereKey((int) $input['course_id'])->exists()) {
                $validator->errors()->add('course_id', 'Selected course is not available for registration.');
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
            'requested_course_id' => ($input['requested_role'] ?? null) === 'student'
                ? (int) $input['course_id']
                : null,
            'password' => $input['password'], // User model has 'hashed' cast
            // approved_at / approved_by stay null until approval
        ]);
    }
}
