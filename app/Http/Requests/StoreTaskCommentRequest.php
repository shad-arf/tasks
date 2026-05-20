<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskCommentRequest extends FormRequest
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
            'comment' => ['nullable', 'string', 'required_without:attachment'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,png,jpg,jpeg,webp,txt,xlsx,xls',
                'required_without:comment',
            ],
        ];
    }
}
