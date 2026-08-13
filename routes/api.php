<?php

use App\Http\Controllers\Api\V1\Category\CategoryController;
use App\Http\Controllers\Api\V1\Checkout\CheckoutController;
use App\Http\Controllers\Api\V1\Customer\CustomerController;
use App\Http\Controllers\Api\V1\Payment\PaymentController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Http\Controllers\Api\V1\Receivable\ReceivableDetailController;
use App\Http\Controllers\Api\V1\Receivable\ReceivableListController;
use App\Http\Controllers\Api\V1\Receivable\ReceivablePaymentController;
use App\Http\Controllers\Api\V1\Receivable\ReceivableSummaryController;
use App\Http\Controllers\Api\V1\Transaction\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/checkout/preview', [CheckoutController::class, 'preview',]);
    Route::post('/payments', [PaymentController::class, 'store',]);
    Route::get('/transactions/summary', [TransactionController::class, 'summary',]);
    Route::get('/transactions', [TransactionController::class, 'index',]);
    Route::get('/transactions/{invoiceNumber}', [TransactionController::class, 'show',]);
    Route::get('/receivables/summary', [ReceivableSummaryController::class, 'summary',]);
    Route::get('/receivables', [ReceivableListController::class, 'index',]);
    Route::get('/receivables/{saleId}', [ReceivableDetailController::class, 'show',]);
    Route::post('/receivables/{saleId}/payments', [ReceivablePaymentController::class, 'store']);
});
