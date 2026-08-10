<?php

namespace App\Http\Requests\Api\V1\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionFilterRequest extends FormRequest
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
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'customer_type' => [
                'nullable',
                Rule::in([
                    'guest',
                    'member',
                ]),
            ],

            'payment_method' => [
                'nullable',
                Rule::in([
                    'cash',
                    'qris',
                ]),
            ],

            'payment_status' => [
                'nullable',
                Rule::in([
                    'unpaid',
                    'partial',
                    'paid',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('date_filter') !== 'custom') {
                return;
            }

            if (! $this->filled('start_date')) {
                $validator->errors()->add(
                    'start_date',
                    'start_date wajib diisi untuk custom date.'
                );
            }

            if (! $this->filled('end_date')) {
                $validator->errors()->add(
                    'end_date',
                    'end_date wajib diisi untuk custom date.'
                );
            }
        });
    }
}
