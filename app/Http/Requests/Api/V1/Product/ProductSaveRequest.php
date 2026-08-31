<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => [
                'nullable',
                'integer',
            ],

            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'sku' => [
                'required',
                'string',
                'max:50',
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'img_url' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'cost_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price_normal' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price_grocier' => [
                'required',
                'numeric',
                'min:0',
            ],

            'unit' => [
                'required',
                'string',
                'max:20',
            ],

            'minimum_stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }
}
