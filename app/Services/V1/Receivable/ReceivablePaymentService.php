<?php

namespace App\Services\V1\Receivable;

use App\Models\ReceivablePayment;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceivablePaymentService
{
    /**
     * @throws \Throwable
     */
    public function pay(
        int     $storeId,
        int     $userId,
        string  $invoiceNumber,
        int     $amount,
        ?string $notes
    ): Sale
    {
        return DB::transaction(function () use (
            $storeId,
            $userId,
            $invoiceNumber,
            $amount,
            $notes
        ) {
            $sale = Sale::query()
                ->where(
                    'store_id',
                    $storeId
                )
                ->where(
                    'invoice_number',
                    $invoiceNumber
                )
                ->lockForUpdate()
                ->first();

            if (!$sale) {
                throw ValidationException::withMessages([
                    'invoice_number' => [
                        'Piutang tidak ditemukan.',
                    ],
                ]);
            }
            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages([
                    'invoice_number' => [
                        'Transaksi tidak dapat menerima pembayaran piutang.',
                    ],
                ]);
            }
            if ($sale->customer_type !== 'member') {
                throw ValidationException::withMessages([
                    'invoice_number' => [
                        'Hanya transaksi member yang dapat memiliki piutang.',
                    ],
                ]);
            }
            if ($sale->due_date === null) {
                throw ValidationException::withMessages([
                    'invoice_number' => [
                        'Transaksi ini bukan transaksi piutang.',
                    ],
                ]);
            }
            if (
                (int)$sale->remaining_balance <= 0
                || $sale->payment_status === 'paid'
            ) {
                throw ValidationException::withMessages([
                    'amount' => [
                        'Piutang transaksi sudah lunas.',
                    ],
                ]);
            }
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => [
                        'Nominal pembayaran harus lebih besar dari 0.',
                    ],
                ]);
            }
            if (
                $amount
                > (int)$sale->remaining_balance
            ) {
                throw ValidationException::withMessages([
                    'amount' => [
                        'Nominal pembayaran melebihi sisa piutang.',
                    ],
                ]);
            }
            ReceivablePayment::create([
                'sale_id' =>
                    $sale->id,

                'user_id' =>
                    $userId,

                'amount' =>
                    $amount,

                'paid_at' =>
                    now(),

                'notes' =>
                    $notes,
            ]);
            $sale->refresh();

            return $sale->load([
                'customer',
                'user',
                'store',

                'receivablePayments' => function ($query) {
                    $query
                        ->with('user:id,name')
                        ->orderBy('paid_at');
                },
            ]);
        }, 3);
    }
}
