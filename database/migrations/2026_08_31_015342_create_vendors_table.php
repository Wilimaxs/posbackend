<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name', 150);
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();

            $table->string('contact_name', 150)->nullable();
            $table->string('contact_phone', 30)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['store_id', 'phone'],
                'vendors_store_phone_unique'
            );

            $table->index([
                'store_id',
                'is_active',
            ]);

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
