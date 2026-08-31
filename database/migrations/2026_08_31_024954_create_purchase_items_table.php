<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('product_name', 150);
            $table->string('sku', 50);
            $table->string('barcode', 100)->nullable();
            $table->string('unit', 20)->default('pcs');

            $table->unsignedInteger('quantity');

            $table->decimal('cost_price', 15);

            $table->timestamps();

            $table->unique([
                'purchase_id',
                'product_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
