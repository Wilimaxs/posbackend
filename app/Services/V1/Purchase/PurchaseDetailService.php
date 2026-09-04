<?php

namespace App\Services\V1\Purchase;

use App\Models\Purchase;
use Illuminate\Validation\ValidationException;

class PurchaseDetailService
{
    public function getDetail(
        int $storeId,
        int $purchaseId
    ): Purchase {
        $purchase = Purchase::query()
            ->with([
                'vendor:id,name,phone,address',
                'user:id,name',
                'items',
            ])
            ->where('store_id', $storeId)
            ->where('id', $purchaseId)
            ->first();

        if (!$purchase) {
            throw ValidationException::withMessages([
                'purchase_id' => [
                    'Pembelian tidak ditemukan.',
                ],
            ]);
        }

        return $purchase;
    }
}
