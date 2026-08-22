<?php

namespace App\Services\V1\Receivable;

use App\Models\Sale;
use Illuminate\Validation\ValidationException;

class ReceivableDetailService
{
    public function getDetail(
        int $storeId,
        int $saleId
    ): Sale
    {
        $sale = Sale::query()
            ->with(['customer:id,customer_code,name,phone,address',
                'user:id,name',
                'items',
                'receivablePayments' => fn($query) => $query
                    ->with('user:id,name')
                    ->orderBy('created_at'),
            ])
            ->whereKey($saleId)
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->where('remaining_balance', '>', 0)
            ->first();

        if (!$sale) {
            throw ValidationException::withMessages([
                'sale_id' => [
                    'Piutang tidak ditemukan.',
                ],
            ]);
        }
        return $sale;
    }
}
