<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    private const MAX_REGISTRATION_DOCUMENT_KILOBYTES = 25600;

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
                'program_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('language_programs', 'id')->where(fn ($query) => $query->where('is_active', true)),
                ],
                'course_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('courses', 'id')->where(function ($query) {
                        $query
                            ->where('price', '>', 0)
                            ->whereNotNull('program_id');
                    }),
                ],
                'registration_document' => ['nullable', 'file', 'max:'.self::MAX_REGISTRATION_DOCUMENT_KILOBYTES],
                'password' => $this->passwordRules(),
            ],
            [
                'program_id.exists' => 'Selected program is not available for registration.',
                'course_id.exists' => 'Selected course is not available for registration.',
            ],
        );

        $validator->after(function ($validator) use ($input) {
            $documentType = $this->requiredDocumentTypeForRole($input['requested_role'] ?? null);
            $uploadedDocument = request()->file('registration_document');

            if ($documentType !== null && ! $uploadedDocument instanceof UploadedFile) {
                $validator->errors()->add(
                    'registration_document',
                    $documentType === 'birth_certificate'
                        ? 'Birth certificate upload is required for student registration.'
                        : 'C.V upload is required for this registration.'
                );
            }

            if ($documentType !== null && $uploadedDocument instanceof UploadedFile) {
                $this->validateRegistrationDocumentExtension($validator, $uploadedDocument, $documentType);
            }

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

            if (empty($input['program_id'])) {
                $validator->errors()->add('program_id', 'Please select a program for student registration.');

                return;
            }

            $selectedProgramId = (int) $input['program_id'];
            $selectedCourseId = (int) $input['course_id'];

            $selectedCourseIsAvailable = Course::query()
                ->available()
                ->whereKey($selectedCourseId)
                ->whereHas('program', function ($query): void {
                    $query->where('is_active', true);
                })
                ->exists();

            if (! $selectedCourseIsAvailable) {
                $validator->errors()->add('course_id', 'Selected course is not available for registration.');

                return;
            }

            $courseMatchesProgram = Course::query()
                ->available()
                ->whereKey($selectedCourseId)
                ->where('program_id', $selectedProgramId)
                ->whereHas('program', function ($query): void {
                    $query->where('is_active', true);
                })
                ->exists();

            if (! $courseMatchesProgram) {
                $validator->errors()->add('course_id', 'Selected course does not belong to the selected program.');
            }
        });

        $validator->validate();

        $parentId = null;
        $documentType = $this->requiredDocumentTypeForRole($input['requested_role'] ?? null);
        $uploadedDocument = request()->file('registration_document');

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

        return DB::transaction(function () use ($documentType, $input, $parentId, $uploadedDocument): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'requested_role' => $input['requested_role'],
                'date_of_birth' => $input['date_of_birth'] ?? null,
                'parent_id' => $parentId,
                'requested_course_id' => ($input['requested_role'] ?? null) === 'student'
                    ? (int) $input['course_id']
                    : null,
                'password' => $input['password'],
            ]);

            $this->storeRegistrationDocument($user, $uploadedDocument, $documentType);

            return $user;
        });
    }

    private function requiredDocumentTypeForRole(?string $role): ?string
    {
        return match ($role) {
            'student' => 'birth_certificate',
            'teacher', 'secretary' => 'cv',
            default => null,
        };
    }

    private function validateRegistrationDocumentExtension($validator, UploadedFile $uploadedDocument, string $documentType): void
    {
        $extension = strtolower((string) $uploadedDocument->getClientOriginalExtension());
        $allowedExtensions = $this->allowedDocumentExtensionsForType($documentType);

        if (in_array($extension, $allowedExtensions, true)) {
            return;
        }

        $validator->errors()->add(
            'registration_document',
            'The registration document must be a '.strtoupper(implode(', ', $allowedExtensions)).' file.'
        );
    }

    /**
     * @return list<string>
     */
    private function allowedDocumentExtensionsForType(string $documentType): array
    {
        return match ($documentType) {
            'birth_certificate' => ['pdf', 'jpg', 'jpeg', 'png'],
            'cv' => ['pdf', 'doc', 'docx'],
            default => [],
        };
    }

    private function storeRegistrationDocument(User $user, UploadedFile|null $uploadedDocument, ?string $documentType): void
    {
        if ($uploadedDocument === null || $documentType === null) {
            return;
        }

        $storedPath = Storage::disk('public')->putFile("registration-documents/{$documentType}/{$user->id}", $uploadedDocument);

        if (! is_string($storedPath) || $storedPath === '') {
            throw ValidationException::withMessages([
                'registration_document' => 'Document upload failed while saving to local storage. Please try again.',
            ]);
        }

        $user->forceFill([
            'registration_document_type' => $documentType,
            'registration_document_original_filename' => $uploadedDocument->getClientOriginalName(),
            'registration_document_path' => $storedPath,
            'registration_document_mime_type' => $uploadedDocument->getClientMimeType(),
            'registration_document_size' => (int) $uploadedDocument->getSize(),
        ])->save();
    }
}
