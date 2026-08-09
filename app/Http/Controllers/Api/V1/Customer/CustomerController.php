<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Customer\CustomerResource;
use App\Services\V1\Customer\CustomerService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        // Sementara hardcode karena login belum dibuat.
        $storeId = 1;

        $customers = $this->customerService->getList(
            storeId: $storeId,
            search: $request->string('search')->toString() ?: null,
            perPage: $request->integer('per_page') ?: 20,
        );

        return ApiResponse::success(
            data: CustomerResource::collection($customers->items()),
            message: 'Daftar member berhasil diambil.',
            meta: [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        );
    }
}
