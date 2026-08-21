<?php

namespace App\Services\V1\Sale;

use App\Models\ProductStock;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaleCancellationService
{
    public function cancel(
        int $saleId
    ): bool
    {
        return DB::transaction(
            function () use ($saleId) {
                // Lock sale untuk dibaca.
                $sale = Sale::query()
                    ->whereKey($saleId)
                    ->lockForUpdate()
                    ->first();

                if (!$sale || $sale->status !== 'pending') {
                    return false;
                }

                $items = $sale->items()->select(['product_id', 'quantity',])
                    ->orderBy('product_id')
                    ->get();

                $productIds = $items->pluck('product_id')->unique()->values();

                /*
                 * Lock stok dalam urutan product_id
                 * yang konsisten.
                 */
                $stocks = ProductStock::query()
                    ->where('store_id', $sale->store_id)
                    ->whereIn('product_id', $productIds)
                    ->orderBy('product_id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($items as $item) {
                    $stock = $stocks->get($item->product_id);

                    if (!$stock) {
                        throw new RuntimeException("Stock product ID {$item->product_id} "
                            . "tidak ditemukan untuk store "
                            . "{$sale->store_id}."
                        );
                    }

                    $stock->increment('stock', (int)$item->quantity);
                }

                // perubahan status sale setelah stok dikembalikan
                $sale->update(['status' => 'cancelled',]);

                return true;
            }, 3);
    }
}
