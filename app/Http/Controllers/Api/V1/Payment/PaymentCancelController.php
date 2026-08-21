<?php
namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Services\V1\Sale\SaleCancellationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PaymentCancelController extends Controller
{
    public function __construct(
        private readonly SaleCancellationService $cancellationService,
    ) {
    }

    public function cancel(
        int $saleId
    ): JsonResponse {

        $cancelled = $this->cancellationService->cancelExpired(
            saleId: $saleId,
        );

        if (!$cancelled) {
            return ApiResponse::error(
                message: 'Rincian pembayaran tidak ditemukan atau sudah tidak dapat dibatalkan.',
                status: 422,
            );
        }

        return ApiResponse::success(
            message: 'Rincian pembayaran berhasil dibatalkan.',
        );
    }
}
