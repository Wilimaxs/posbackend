<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('opname_number', 50)->unique();

            $table->unsignedInteger('total_products');

            $table->enum('status', [
                'ongoing',
                'completed',
            ])->default('ongoing');

            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index([
                'store_id',
                'status',
            ]);

            $table->index([
                'store_id',
                'started_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
