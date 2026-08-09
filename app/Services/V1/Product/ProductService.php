<?php

namespace App\Services\V1\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getList(
        int     $storeId,
        ?string $search = null,
        ?int    $categoryId = null,
        int     $perPage = 20
    ): LengthAwarePaginator
    {
        return Product::query()
            ->with([
                'category',
                'productStocks' => function ($query) use ($storeId) {
                    $query
                        ->where('store_id', $storeId)
                        ->with('discount');
                },
            ])
            ->where('is_active', true)
            ->whereHas('productStocks', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%$search%")
                        ->orWhere('sku', 'like', "%$search%")
                        ->orWhere('barcode', $search);
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->paginate($perPage);
    }
}
