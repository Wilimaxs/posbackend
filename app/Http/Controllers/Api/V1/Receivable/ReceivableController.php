<?php

namespace App\Http\Controllers\Api\V1\Receivable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Receivable\ReceivableFilterRequest;
use App\Http\Resources\Api\V1\Receivable\ReceivableDetailResource;
use App\Http\Resources\Api\V1\Receivable\ReceivableResource;
use App\Http\Resources\Api\V1\Receivable\ReceivableSummaryResource;
use App\Services\V1\Receivable\ReceivableDetailService;
use App\Services\V1\Receivable\ReceivableListService;
use App\Services\V1\Receivable\ReceivableSummaryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceivableController extends Controller
{
    public function __construct(
        private readonly ReceivableListService    $listService,
        private readonly ReceivableDetailService  $detailService,
    )
    {
    }

    public function index(
        ReceivableFilterRequest $request
    ): JsonResponse
    {
        $storeId = 1;

        $receivables =
            $this->listService->getReceivables(
                storeId: $storeId,
                filters: $request->validated(),
            );

        $data =
            ReceivableResource::collection(
                $receivables->getCollection()
            )->resolve($request);

        return ApiResponse::success(
            data: $data,

            message: 'Daftar piutang berhasil diambil.',

            meta: [
                'current_page' =>
                    $receivables->currentPage(),

                'last_page' =>
                    $receivables->lastPage(),

                'per_page' =>
                    $receivables->perPage(),

                'total' =>
                    $receivables->total(),
            ],
        );
    }

    public function show(
        Request $request,
        string  $invoiceNumber
    ): JsonResponse
    {
        $storeId = 1;

        $sale =
            $this->detailService->getDetail(
                storeId: $storeId,
                invoiceNumber: $invoiceNumber,
            );

        return ApiResponse::success(
            data: ReceivableDetailResource::make(
                $sale
            )->resolve($request),

            message: 'Detail piutang berhasil diambil.',
        );
    }
}
