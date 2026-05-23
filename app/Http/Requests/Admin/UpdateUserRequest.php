<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    protected $errorBag = 'updateUser';

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->normalizePhone($this->input('phone')),
            'business_name' => $this->resolveBusinessName(),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|Rule>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['nullable', 'string', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'digits_between:8,15'],
            'business_name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['manager', 'user'])],
            'password' => ['nullable', 'string'],
        ];
    }

    private function normalizePhone(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', $value) ?? '';

        return $normalized !== '' ? $normalized : null;
    }

    private function resolveBusinessName(): ?string
    {
        $businessName = $this->input('business_name');

        if (is_string($businessName) && trim($businessName) !== '') {
            return trim($businessName);
        }

        $selection = $this->input('business_selection');

        if ($selection === '__new__') {
            $newBusinessName = $this->input('new_business_name');

            return is_string($newBusinessName) && trim($newBusinessName) !== ''
                ? trim($newBusinessName)
                : null;
        }

        return is_string($selection) && trim($selection) !== ''
            ? trim($selection)
            : null;
    }
}
