<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('purchase_number', 50);

            $table->string('vendor_reference', 100);

            $table->date('purchase_date');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'store_id',
                'purchase_date',
            ]);

            $table->index([
                'store_id',
                'created_at',
            ]);

            $table->index([
                'store_id',
                'vendor_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
