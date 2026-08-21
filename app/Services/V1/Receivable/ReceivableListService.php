<?php

namespace App\Services\V1\Receivable;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReceivableListService
{
    public function getList(
        int   $storeId,
        array $filters
    ): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $dueStatus = $filters['due_status'] ?? null;
        $sort = $filters['sort'] ?? 'nearest';

        return Sale::query()
            ->with(['customer:id,name,customer_code,phone',])
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->where('remaining_balance', '>', 0)
            ->when($search, function ($query, $search) {
                $query
                    ->where(
                        function ($query) use ($search) {
                            $query
                                ->where('invoice_number', 'like', "%{$search}%")
                                ->orWhereHas('customer', fn($query) => $query
                                    ->where('name', 'like', "%{$search}%")
                                );
                        });
            })
            ->when($dueStatus === 'today', fn($query) => $query
                ->whereDate('due_date', today())
            )
            ->when($dueStatus === 'overdue', fn($query) => $query
                ->whereDate('due_date', '<', today())
            )
            ->when($dueStatus === 'active', fn($query) => $query
                ->whereDate('due_date', '>', today())
            )
            ->when($sort === 'nearest', fn($query) => $query
                ->orderByRaw(
                    'ABS(DATEDIFF(due_date, ?)) ASC',
                    [today()->toDateString()]
                )
            )
            ->when($sort === 'farthest', fn($query) => $query
                ->orderByRaw(
                    'ABS(DATEDIFF(due_date, ?)) DESC',
                    [today()->toDateString()]
                )
            )
            ->orderByDesc('id')
            ->orderByDesc('id')
            ->paginate(20);
    }
}
