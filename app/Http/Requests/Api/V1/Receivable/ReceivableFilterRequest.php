<?php

namespace App\Http\Requests\Api\V1\Receivable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceivableFilterRequest extends FormRequest
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
                    'active',
                    'due_today',
                    'overdue',
                ]),
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'newest',
                    'oldest',
                    'due_date_asc',
                    'due_date_desc',
                    'balance_asc',
                    'balance_desc',
                ]),
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }
}
