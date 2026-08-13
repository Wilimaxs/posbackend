<?php

namespace App\Http\Controllers\Api\V1\Receivable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Receivable\ReceivableListRequest;
use App\Http\Resources\Api\V1\Receivable\ReceivableListResource;
use App\Services\V1\Receivable\ReceivableListService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReceivableListController extends Controller
{
    public function __construct(
        private readonly ReceivableListService $listService
    )
    {
    }

    public function index(
        ReceivableListRequest $request
    ): JsonResponse
    {
        $storeId = 1;

        $receivables = $this->listService->getList(
            storeId: $storeId,
            filters: $request->validated(),
        );

        return ApiResponse::success(
            data: ReceivableListResource::collection(
                $receivables->items()
            ),
            message: 'Daftar piutang berhasil diambil.',
            meta: [
                'current_page' => $receivables->currentPage(),
                'last_page' => $receivables->lastPage(),
                'per_page' => $receivables->perPage(),
                'total' => $receivables->total(),
            ],
        );
    }
}
