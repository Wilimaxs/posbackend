<?php

namespace App\Services\V1\Payment;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentService
{
    /**
     * @throws Throwable
     */
    public function process(
        int   $storeId,
        int   $userId,
        array $data,
    ): Sale
    {
        return DB::transaction(function () use (
            $storeId,
            $userId,
            $data
        ) {
            // Lock sale untuk dibaca. sama dengan agar tidak bentrok dengan scheduler
            $sale = Sale::query()
                ->whereKey($data['sale_id'])
                ->where('store_id', $storeId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$sale) {
                throw ValidationException::withMessages([
                    'sale_id' => ['Transaksi tidak ditemukan.'],
                ]);
            }

            // Idempotent retry. jika transaksi sudah selesai, tidak perlu diproses lagi.
            if ($sale->status === 'completed') {
                return $this->loadSale($sale);
            }

            // jika transaksi sudah tidak dapat dibayar, tidak perlu diproses lagi.
            if ($sale->status !== 'pending') {
                throw ValidationException::withMessages([
                    'sale_id' => [
                        'Transaksi sudah tidak dapat dibayar.',
                    ],
                ]);
            }

            // jika transaksi sudah kedaluwarsa, tidak perlu diproses lagi. ini untuk backup jika scheduler tidak berjalan.
            if ($sale->created_at->lte(now()->subMinutes(5))) {
                throw ValidationException::withMessages([
                    'sale_id' => [
                        'Transaksi sudah kedaluwarsa.',
                    ],
                ]);
            }

            $items = $sale->items()->get();

            $total = $items->sum(fn($item) => (
                    (int)$item->unit_price * (int)$item->quantity) - (int)$item->discount_value
            );

            $paymentAmount = (int)$data['payment_amount'];

            // jika customer adalah guest, maka harus melakukan pembayaran penuh.
            if ($sale->customer_type === 'guest' && $paymentAmount < $total) {
                throw ValidationException::withMessages([
                    'payment_amount' => [
                        'Guest harus melakukan pembayaran penuh.',
                    ],
                ]);
            }

            $remainingBalance = max($total - $paymentAmount, 0);

            // jika customer adalah member, maka harus mengisi due_date jika melakukan pembayaran sebagian.
            if ($remainingBalance > 0 && empty($data['due_date'])) {
                throw ValidationException::withMessages([
                    'due_date' => [
                        'Tanggal jatuh tempo wajib diisi untuk pembayaran sebagian.',
                    ],
                ]);
            }

            /*
             * Nilai yang benar-benar masuk transaksi.
             *
             * bayar = 100.000
             * total = 90.000
             *
             * initial_payment = 90.000
             * change_amount   = 10.000
             */
            $initialPayment = min($paymentAmount, $total);

            $changeAmount = max($paymentAmount - $total, 0);

            $isPaid = $remainingBalance === 0;

            $sale->update([
                'invoice_number' => $this->generateInvoiceNumber(),

                'payment_method' => $data['payment_method'],

                'initial_payment' => $initialPayment,

                'change_amount' => $changeAmount,

                'remaining_balance' => $remainingBalance,

                'payment_status' => $isPaid ? 'paid' : 'partial',

                'due_date' => $isPaid ? null : $data['due_date'],

                'status' => 'completed',

                'paid_at' => $isPaid ? now() : null,

                'notes' => $data['notes'] ?? null,
            ]);
            return $this->loadSale($sale);
        }, 3);
    }

    private function loadSale(
        Sale $sale
    ): Sale
    {
        return $sale->load([
            'store',
            'user',
            'customer',
            'items',
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . Str::upper((string)Str::ulid());
    }
}
