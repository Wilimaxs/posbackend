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
            /*
             * ============================
             * CUSTOMER
             * ============================
             *
             * null = Guest
             * ada ID = Member
             */
            'customer_id' => [
                'nullable',
                'integer',
            ],

            /*
             * ============================
             * TOTAL TRANSAKSI
             * ============================
             *
             * Semua nominal ini SUDAH dihitung
             * dan dikunci dari mobile.
             */

            'total_before_discount' => [
                'required',
                'integer',
                'min:0',
            ],

            'total_discount' => [
                'required',
                'integer',
                'min:0',
            ],

            'total_after_discount' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * ============================
             * PAYMENT
             * ============================
             */

            'paid_amount' => [
                'required',
                'integer',
                'min:0',
            ],

            'remaining_balance' => [
                'required',
                'integer',
                'min:0',
            ],

            'payment_status' => [
                'required',
                'in:unpaid,partial,paid',
            ],

            'payment_method' => [
                'required',
                /*
                 * Todo: ganti setelah QRIS selesai: in:cash,qris
                */
                'in:cash',
            ],

            'due_date' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /*
             * ============================
             * ITEMS
             * ============================
             */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
            ],

            'items.*.product_name' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.sku' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.barcode' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.unit' => [
                'required',
                'string',
                'max:50',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            /*
             * Snapshot harga dari mobile.
             */
            'items.*.cost_price' => [
                'required',
                'integer',
                'min:0',
            ],

            'items.*.unit_price' => [
                'required',
                'integer',
                'min:0',
            ],

            'items.*.price_type' => [
                'required',
                'in:normal,grocier',
            ],

            /*
             * Subtotal juga sudah dihitung mobile.
             */
            'items.*.subtotal' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * Discount boleh null.
             */
            'items.*.discount' => [
                'nullable',
                'array',
            ],

            'items.*.discount.id' => [
                'nullable',
                'integer',
            ],

            'items.*.discount.name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.discount.value' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'items.*.subtotal_after_discount' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }
}
