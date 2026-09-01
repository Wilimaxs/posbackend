<?php

namespace App\Http\Controllers\Api\V1\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Purchase\PurchaseListRequest;
use App\Http\Resources\Api\V1\Purchase\PurchaseListResource;
use App\Services\V1\Purchase\PurchaseListService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PurchaseListController extends Controller
{
    public function __construct(
        private readonly PurchaseListService $listService
    ) {
    }

    public function index(
        PurchaseListRequest $request
    ): JsonResponse {
        $storeId = 1;

        $purchases = $this->listService->getList(
            storeId: $storeId,
            filters: $request->validated(),
        );

        return ApiResponse::success(
            data: PurchaseListResource::collection(
                $purchases->items()
            ),
            message: 'Daftar pembelian berhasil diambil.',
            meta: [
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
                'per_page' => $purchases->perPage(),
                'total' => $purchases->total(),
            ],
        );
    }
}
