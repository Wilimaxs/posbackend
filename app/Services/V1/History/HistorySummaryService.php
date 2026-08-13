<?php

namespace App\Services\V1\History;

use App\Models\Sale;

class HistorySummaryService
{
    public function getTodaySummary(int $storeId): array
    {
        $summary = Sale::query()
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->selectRaw('
                COUNT(*) as total_transactions,

                COALESCE(
                    SUM(initial_payment + remaining_balance),
                    0
                ) as total_sales,

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

        return [
            'total_transactions' => (int)$summary->total_transactions,
            'total_sales' => (int)$summary->total_sales,
            'cash_payment' => (int)$summary->cash_payment,
            'qris_payment' => (int)$summary->qris_payment,
        ];
    }
}
