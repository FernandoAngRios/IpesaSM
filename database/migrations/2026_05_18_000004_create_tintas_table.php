<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tintas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->string('color_hex', 7)->nullable(); // #RRGGBB
            $table->decimal('stock_litros', 10, 3)->default(0);
            $table->decimal('stock_minimo', 10, 3)->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tintas');
    }
};
