<?php

namespace App\Services\V1\History;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class HistoryListService
{
    public function getList(
        int $storeId,
        array $filters
    ): LengthAwarePaginator {
        $search = $filters['search'] ?? null;
        $dateFilter = $filters['date_filter'] ?? 'all';
        $paymentStatus = $filters['payment_status'] ?? null;

        $startDate = null;
        $endDate = null;

        if ($dateFilter !== 'all') {
            [$startDate, $endDate] = match ($dateFilter) {
                'today' => [
                    today()->startOfDay(),
                    today()->endOfDay(),
                ],
                'yesterday' => [
                    today()->subDay()->startOfDay(),
                    today()->subDay()->endOfDay(),
                ],
                'last_7_days' => [
                    today()->subDays(6)->startOfDay(),
                    now()->endOfDay(),
                ],
                'this_month' => [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ],
                'custom' => [
                    Carbon::parse(
                        $filters['start_date']
                    )->startOfDay(),
                    Carbon::parse(
                        $filters['end_date']
                    )->endOfDay(),
                ],
            };
        }
        return Sale::query()
            ->select('sales.*')
            ->selectSub(
                SaleItem::query()
                    ->selectRaw('
                        COALESCE(
                            SUM(
                                (quantity * unit_price)
                                - discount_value
                            ),
                            0
                        )
                    ')
                    ->whereColumn(
                        'sale_items.sale_id',
                        'sales.id'
                    ),
                'total_after_discount'
            )
            ->with([
                'customer:id,name',
            ])
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->when($search, fn ($query, $search) =>
                $query
                    ->where(fn ($query) =>
                    $query
                        ->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($query) =>
                            $query
                                ->where('name', 'like', "%{$search}%")
                        )
                )
            )
            ->when($startDate && $endDate, fn ($query) =>
                $query
                    ->whereBetween('created_at', [$startDate, $endDate,]
                )
            )
            ->when($paymentStatus, fn ($query, $paymentStatus) =>
                $query
                    ->where('payment_status', $paymentStatus)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);
    }
}
