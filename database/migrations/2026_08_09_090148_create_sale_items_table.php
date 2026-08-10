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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('product_name');
            $table->string('sku');
            $table->string('barcode')->nullable();
            $table->string('unit')->default('pcs');
            $table->unsignedInteger('quantity');
            $table->decimal('cost_price', 15);
            $table->decimal('unit_price', 15);
            $table->enum('price_type', ['normal', 'grocier',]);
            $table->decimal('subtotal', 15);
            $table->foreignId('discount_id')->nullable()->constrained('discounts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('discount_name')->nullable();
            $table->decimal('discount_value', 15)->default(0);
            $table->decimal('subtotal_after_discount', 15);
            $table->timestamps();

            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
