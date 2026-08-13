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
    public function create(
        int   $storeId,
        int   $userId,
        int   $saleId,
        array $data,
    ): ReceivablePayment
    {
        return DB::transaction(function () use (
            $storeId,
            $userId,
            $saleId,
            $data
        ) {
            $sale = Sale::query()
                ->whereKey($saleId)
                ->where('store_id', $storeId)
                ->where('status', 'completed')
                ->where('remaining_balance', '>', 0)
                ->lockForUpdate()
                ->first();

            if (!$sale) {
                throw ValidationException::withMessages([
                    'sale_id' => [
                        'Piutang tidak ditemukan atau sudah lunas.',
                    ],
                ]);
            }

            $amount = (int)$data['amount'];

            if ($amount > (int)$sale->remaining_balance) {
                throw ValidationException::withMessages([
                    'amount' => [
                        'Nominal pembayaran melebihi sisa piutang.',
                    ],
                ]);
            }

            return ReceivablePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'amount' => $amount,
                'notes' => $data['notes'] ?? null,
            ]);
        }, 3);
    }
}
