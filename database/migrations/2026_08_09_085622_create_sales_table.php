<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnUpdate()->nullOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->enum('customer_type', ['guest', 'member',]);
            $table->enum('payment_method', ['cash', 'qris',])->default('cash');
            $table->decimal('initial_payment', 15)->default(0); // bayar awal
            $table->decimal('change_amount', 15)->nullable(); // uang kembalian jika cash
            $table->decimal('remaining_balance', 15)->default(0); // uang yang masih belum dibayar
            $table->enum('payment_status', ['unpaid', 'partial', 'paid',])->default('unpaid'); // partial == piutang
            $table->date('due_date')->nullable(); // tanggal jatuh tempo jika piutang
            $table->enum('status', ['pending', 'completed', 'cancelled',])->default('pending');
            $table->timestamp('paid_at')->nullable(); // waktu jika pembayaran lunas
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'created_at',]);
            $table->index(['store_id', 'status',]);
            $table->index(['store_id', 'payment_status', 'due_date']);
            $table->index(['store_id', 'payment_method',]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
