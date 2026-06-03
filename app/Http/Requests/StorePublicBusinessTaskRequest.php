<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicBusinessTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:5000'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'image' => ['required', 'image', 'max:10240'],
        ];
    }
}
