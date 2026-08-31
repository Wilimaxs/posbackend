<?php

namespace App\Services\V1\Vendor;

use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorListService
{
    public function getList(
        int $storeId,
        array $filters
    ): LengthAwarePaginator {
        $search = $filters['search'] ?? null;

        return Vendor::query()
            ->where('store_id', $storeId)
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);
    }
}
