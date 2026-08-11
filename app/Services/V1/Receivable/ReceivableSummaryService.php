<?php

namespace App\Services\V1\Receivable;

use App\Models\ReceivablePayment;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;

class ReceivableSummaryService
{
    public function getSummary(
        int $storeId
    ): array {
        $receivableQuery = Sale::query()
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->where('remaining_balance', '>', 0);

        $totalActiveAmount = (int) (
        (clone $receivableQuery)
            ->sum('remaining_balance')
        );

        $totalActiveCount =
            (clone $receivableQuery)
                ->count();

        $dueTodayQuery =
            (clone $receivableQuery)
                ->whereDate(
                    'due_date',
                    today()
                );

        $dueTodayAmount = (int) (
        (clone $dueTodayQuery)
            ->sum('remaining_balance')
        );

        $dueTodayCount =
            (clone $dueTodayQuery)
                ->count();

        $overdueQuery =
            (clone $receivableQuery)
                ->whereDate(
                    'due_date',
                    '<',
                    today()
                );

        $overdueAmount = (int) (
        (clone $overdueQuery)
            ->sum('remaining_balance')
        );

        $overdueCount =
            (clone $overdueQuery)
                ->count();

        $paymentsTodayQuery =
            ReceivablePayment::query()
                ->whereHas(
                    'sale',
                    function (Builder $query) use ($storeId) {
                        $query->where(
                            'store_id',
                            $storeId
                        );
                    }
                )
                ->whereDate(
                    'paid_at',
                    today()
                );

        $paymentsTodayAmount = (int) (
        (clone $paymentsTodayQuery)
            ->sum('amount')
        );

        $paymentsTodayCount =
            (clone $paymentsTodayQuery)
                ->count();

        return [
            'total_active' => [
                'amount' =>
                    $totalActiveAmount,

                'count' =>
                    $totalActiveCount,
            ],

            'due_today' => [
                'amount' =>
                    $dueTodayAmount,

                'count' =>
                    $dueTodayCount,
            ],

            'overdue' => [
                'amount' =>
                    $overdueAmount,

                'count' =>
                    $overdueCount,
            ],

            'payments_today' => [
                'amount' =>
                    $paymentsTodayAmount,

                'count' =>
                    $paymentsTodayCount,
            ],
        ];
    }
}
