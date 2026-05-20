<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    protected $errorBag = 'createUser';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|Rule>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['manager', 'user'])],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
