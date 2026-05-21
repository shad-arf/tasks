<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(['urgent', 'high', 'low'])],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'send_whatsapp' => ['nullable', 'boolean'],
            'whatsapp_message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
