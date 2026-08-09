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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
            $table->decimal('discount_value', 15);
            $table->decimal('minimum_purchase', 15)->default(0);
            $table->enum('customer_scope', ['all', 'guest', 'member'])->default('all');
            $table->dateTime('starts_date');
            $table->dateTime('ends_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('store_id');
            $table->index(['starts_date', 'ends_date']);
            $table->index(['store_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
