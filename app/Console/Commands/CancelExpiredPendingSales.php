<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Services\V1\Sale\SaleCancellationService;
use Illuminate\Console\Command;

class CancelExpiredPendingSales extends Command
{
    protected $signature = 'sales:cancel-expired-pending';

    protected $description = 'Batalkan transaksi pending yang berumur minimal 5 menit dan kembalikan stoknya.';

    public function handle(
        SaleCancellationService $cancellationService
    ): int
    {
        Sale::query()->where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(5))
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($sales) use ($cancellationService) {
                foreach ($sales as $sale) {
                    $cancellationService->cancel($sale->id);
                }
            });
        return self::SUCCESS;
    }
}
