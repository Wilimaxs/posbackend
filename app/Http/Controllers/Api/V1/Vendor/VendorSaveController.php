<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Vendor\VendorSaveRequest;
use App\Services\V1\Vendor\VendorSaveService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class VendorSaveController extends Controller
{
    public function __construct(
        private readonly VendorSaveService $saveService
    ) {
    }

    public function store(
        VendorSaveRequest $request
    ): JsonResponse {
        $storeId = 1;

        $this->saveService->save(
            storeId: $storeId,
            data: $request->validated(),
        );

        return ApiResponse::success(
            message: 'Vendor berhasil disimpan.',
        );
    }
}
