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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->nullOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 100)->unique()->nullable();
            $table->string('name', 150);
            $table->string('img_url', 255)->nullable();
            $table->text('description')->nullable();
            $table->decimal('cost_price', 15);
            $table->decimal('selling_price_normal', 15);
            $table->decimal('selling_price_grocier', 15);
            $table->string('unit', 20)->default('pcs');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index(['category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
