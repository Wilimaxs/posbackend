<?php

namespace App\Http\Requests\Api\V1\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => [
                'required',
                'integer',
            ],
            // Berapa customer bayar
            'payment_amount' => [
                'required',
                'integer',
                'min:1',
            ],

            'payment_method' => [
                'required',
                'in:cash',
            ],
            // Tanggal jatuh tempo kalau piutang
            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
