<?php

namespace App\Services\V1\History;

use App\Models\Sale;
use App\Models\SaleItem;

class HistorySummaryService
{
    public function getTodaySummary(int $storeId): array
    {
        $sales = Sale::query()
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->selectRaw('
                COUNT(*) as total_transactions,

                COALESCE(
                    SUM(
                        CASE
                            WHEN payment_method = "cash"
                            THEN initial_payment
                            ELSE 0
                        END
                    ),
                    0
                ) as cash_payment,

                COALESCE(
                    SUM(
                        CASE
                            WHEN payment_method = "qris"
                            THEN initial_payment
                            ELSE 0
                        END
                    ),
                    0
                ) as qris_payment
            ')
            ->first();

        $totalSales = SaleItem::query()
            ->whereHas(
                'sale',
                fn($query) => $query
                    ->where('store_id', $storeId)
                    ->where('status', 'completed')
                    ->whereDate('created_at', today())
            )
            ->selectRaw('
                COALESCE(
                    SUM(
                        (quantity * unit_price)
                        - discount_value
                    ),
                    0
                ) as total
            ')
            ->value('total');

        return [
            'total_transactions' =>
                (int)$sales->total_transactions,

            'total_sales' =>
                (int)$totalSales,

            'cash_payment' =>
                (int)$sales->cash_payment,

            'qris_payment' =>
                (int)$sales->qris_payment,
        ];
    }
}
