<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursal_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('stock_litros', 10, 3)->default(0);
            $table->timestamps();

            $table->unique(['sucursal_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursal_producto');
    }
};
