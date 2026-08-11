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
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount', 15);
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'paid_at',]);
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};
