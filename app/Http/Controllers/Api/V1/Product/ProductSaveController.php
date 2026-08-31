<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\ProductSaveRequest;
use App\Services\V1\Product\ProductSaveService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductSaveController extends Controller
{
    public function __construct(
        private readonly ProductSaveService $saveService
    ) {
    }

    public function store(
        ProductSaveRequest $request
    ): JsonResponse {
        $storeId = 1;

        $this->saveService->save(
            storeId: $storeId,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: null,
            message: 'Produk berhasil disimpan.',
        );
    }
}
