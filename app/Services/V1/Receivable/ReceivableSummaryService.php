<?php

namespace App\Services\V1\Receivable;

use App\Models\ReceivablePayment;
use App\Models\Sale;

class ReceivableSummaryService
{
    public function getSummary(int $storeId): array
    {
        $today = today()->toDateString();

        $receivable = Sale::query()
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->where('remaining_balance', '>', 0)
            ->selectRaw(
                '
                COUNT(*) as total_active_count,
                COALESCE(SUM(remaining_balance), 0) as total_active_amount,
                SUM(
                    CASE
                      WHEN due_date = ?
                        THEN 1
                        ELSE 0
                    END
                ) as due_today_count,

                COALESCE(
                    SUM(
                        CASE
                            WHEN due_date = ?
                            THEN remaining_balance
                            ELSE 0
                        END
                    ),
                    0
                ) as due_today_amount,

                SUM(
                    CASE
                        WHEN due_date < ?
                        THEN 1
                        ELSE 0
                    END
                ) as overdue_count,

                COALESCE(
                    SUM(
                        CASE
                            WHEN due_date < ?
                            THEN remaining_balance
                            ELSE 0
                        END
                    ),
                    0
                ) as overdue_amount
                ',
                [$today, $today, $today, $today,]
            )->first();

        $paymentsToday = ReceivablePayment::query()
            ->whereHas('sale', fn($query) => $query->where('store_id', $storeId))
            ->whereDate('created_at', $today)
            ->selectRaw(
                '
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as amount
                '
            )->first();

        return [
            'total_active' => [
                'amount' => (int)$receivable->total_active_amount,
                'count' => (int)$receivable->total_active_count,
            ],

            'due_today' => [
                'amount' => (int)$receivable->due_today_amount,
                'count' => (int)$receivable->due_today_count,
            ],

            'overdue' => [
                'amount' => (int)$receivable->overdue_amount,
                'count' => (int)$receivable->overdue_count,
            ],

            'payments_today' => [
                'amount' => (int)$paymentsToday->amount,
                'count' => (int)$paymentsToday->count,
            ],
        ];
    }
}
