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
