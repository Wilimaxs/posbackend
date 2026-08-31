<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Vendor\VendorListRequest;
use App\Http\Resources\Api\V1\Vendor\VendorListResource;
use App\Services\V1\Vendor\VendorListService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class VendorListController extends Controller
{
    public function __construct(
        private readonly VendorListService $listService
    ) {
    }

    public function index(
        VendorListRequest $request
    ): JsonResponse {
        $storeId = 1;

        $vendors = $this->listService->getList(
            storeId: $storeId,
            filters: $request->validated(),
        );

        return ApiResponse::success(
            data: VendorListResource::collection(
                $vendors->items()
            ),
            message: 'Daftar vendor berhasil diambil.',
            meta: [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
            ],
        );
    }
}
