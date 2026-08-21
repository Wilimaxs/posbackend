<?php

namespace App\Services\V1\Customer;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getList(
        int     $storeId,
        ?string $search = null,
    ): LengthAwarePaginator
    {
        return Customer::query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->withCount([
                'sales as receivable_transactions_count' => function ($query) {
                    $query
                        ->where('status', 'completed')
                        ->whereIn('payment_status', ['unpaid', 'partial'])
                        ->where('remaining_balance', '>', 0);
                }
            ])
            ->withSum([
                'sales as receivable_total' => function ($query) {
                    $query
                        ->where('status', 'completed')
                        ->whereIn('payment_status', ['unpaid', 'partial'])
                        ->where('remaining_balance', '>', 0);
                }
            ], 'remaining_balance')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('customer_code', 'like', "%$search%");
                });
            })
            ->orderBy('name')
            ->paginate(20);
    }
}
