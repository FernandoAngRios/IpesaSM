<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origen_id')->constrained('sucursales');
            $table->foreignId('destino_id')->constrained('sucursales');
            $table->foreignId('product_id')->constrained();
            $table->decimal('cantidad_litros', 10, 3);
            $table->text('nota')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};
