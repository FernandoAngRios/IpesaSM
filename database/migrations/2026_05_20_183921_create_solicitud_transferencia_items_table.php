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
        Schema::create('solicitud_transferencia_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitud_transferencias')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('cantidad_solicitada', 10, 3);
            $table->decimal('cantidad_enviada', 10, 3)->nullable();
            $table->decimal('cantidad_recibida', 10, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_transferencia_items');
    }
};
