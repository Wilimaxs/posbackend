<?php

namespace App\Http\Requests\Api\V1\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseListRequest extends FormRequest
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
                'max:50',
            ],

            'vendor_id' => [
                'nullable',
                'integer',
            ],

            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],
        ];
    }
}
