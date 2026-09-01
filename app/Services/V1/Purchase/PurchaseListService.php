<?php

namespace App\Services\V1\Purchase;

use App\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseListService
{
    public function getList(
        int $storeId,
        array $filters
    ): LengthAwarePaginator {
        $search = $filters['search'] ?? null;
        $vendorId = $filters['vendor_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        return Purchase::query()
            ->where('store_id', $storeId)

            ->withAggregate(
                'vendor as vendor_name',
                'name'
            )

            ->withSum(
                'items as total_purchase',
                DB::raw('quantity * cost_price')
            )

            ->when(
                $search,
                fn ($query, $search) =>
                $query->where(
                    'purchase_number',
                    'like',
                    "%{$search}%"
                )
            )

            ->when(
                $vendorId,
                fn ($query, $vendorId) =>
                $query->where('vendor_id', $vendorId)
            )

            ->when(
                $dateFrom,
                fn ($query, $dateFrom) =>
                $query->whereDate(
                    'purchase_date',
                    '>=',
                    $dateFrom
                )
            )

            ->when(
                $dateTo,
                fn ($query, $dateTo) =>
                $query->whereDate(
                    'purchase_date',
                    '<=',
                    $dateTo
                )
            )

            ->orderByDesc('id')
            ->paginate(20);
    }
}
