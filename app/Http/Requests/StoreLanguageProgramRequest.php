<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLanguageProgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('language-programs.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', 'alpha_dash', Rule::unique('language_programs', 'code')],
            'locale_code' => ['nullable', 'string', 'max:12'],
            'name' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:300'],
            'full_description' => ['required', 'string', 'max:5000'],
            'flag_url' => ['nullable', 'url', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
