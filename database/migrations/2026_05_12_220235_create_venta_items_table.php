<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('venta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_presentation_id')->nullable()->constrained('product_presentations')->nullOnDelete();
            // Snapshot para que el historial no cambie si el producto se edita
            $table->string('nombre_producto');
            $table->string('nombre_presentacion')->nullable();
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('cantidad', 10, 3);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_items');
    }
};
