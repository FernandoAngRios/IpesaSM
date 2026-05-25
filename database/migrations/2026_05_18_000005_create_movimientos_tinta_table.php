<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_tinta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tinta_id')->constrained('tintas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'uso', 'ajuste']);
            $table->decimal('cantidad_litros', 10, 3);
            $table->string('referencia')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_tinta');
    }
};
