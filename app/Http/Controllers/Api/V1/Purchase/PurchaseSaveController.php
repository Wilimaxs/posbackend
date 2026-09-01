<?php

namespace App\Http\Controllers\Api\V1\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Purchase\PurchaseSaveRequest;
use App\Services\V1\Purchase\PurchaseSaveService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PurchaseSaveController extends Controller
{
    public function __construct(
        private readonly PurchaseSaveService $saveService
    ) {
    }

    public function store(
        PurchaseSaveRequest $request
    ): JsonResponse {
        $storeId = 1;
        $userId = 1;

        $this->saveService->save(
            storeId: $storeId,
            userId: $userId,
            data: $request->validated(),
        );

        return ApiResponse::success(
            message: 'Pembelian berhasil disimpan.',
        );
    }
}
