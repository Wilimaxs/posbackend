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
            $table->decimal('total_before_discount', 15);
            $table->decimal('total_discount', 15)->default(0);
            $table->decimal('total_after_discount', 15);
            $table->decimal('paid_amount', 15)->default(0);
            $table->decimal('remaining_balance', 15)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid',])->default('unpaid');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled',])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'created_at',]);
            $table->index(['store_id', 'status',]);
            $table->index('customer_id');
            $table->index('user_id');
            $table->index('due_date');
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
