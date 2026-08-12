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
            $table->decimal('cost_price', 15); // modal
            $table->decimal('unit_price', 15); // dijual 1pcs
            $table->enum('price_type', ['normal', 'grocier',]); // tipe harga yang dijual
            $table->foreignId('discount_id')->nullable()->constrained('discounts')->cascadeOnUpdate()->nullOnDelete();
            $table->string('discount_name')->nullable();
            $table->decimal('discount_value', 15)->default(0);
            $table->timestamps();
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
