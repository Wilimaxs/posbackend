<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Product\ProductResource;
use App\Services\V1\Product\ProductService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $storeId = 1;

        $products = $this->productService->getList(
            storeId: $storeId,
            search: $request->string('search')->toString() ?: null,
            categoryId: $request->integer('category_id') ?: null,
            includeInactive: $request->boolean('include_inactive'),
        );

        return ApiResponse::success(
            data: ProductResource::collection($products->items()),
            message: 'Daftar produk berhasil diambil.',
            meta: [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        );
    }
}
