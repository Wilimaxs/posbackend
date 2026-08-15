<?php

namespace App\Http\Requests\Api\V1\History;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HistoryListRequest extends FormRequest
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

            'date_filter' => [
                'nullable',
                Rule::in([
                    'all',
                    'today',
                    'yesterday',
                    'last_7_days',
                    'this_month',
                    'custom',
                ]),
            ],

            'start_date' => [
                'nullable',
                'required_if:date_filter,custom',
                'date',
            ],

            'end_date' => [
                'nullable',
                'required_if:date_filter,custom',
                'date',
                'after_or_equal:start_date',
            ],

            'payment_status' => [
                'nullable',
                Rule::in([
                    'partial',
                    'paid',
                ]),
            ],
        ];
    }
}
