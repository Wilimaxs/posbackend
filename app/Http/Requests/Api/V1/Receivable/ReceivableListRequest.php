<?php

namespace App\Http\Requests\Api\V1\Receivable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceivableListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'due_status' => [
                'nullable',
                Rule::in([
                    'today',
                    'overdue',
                    'active',
                ]),
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'nearest',
                    'farthest',
                ]),
            ],
        ];
    }
}
