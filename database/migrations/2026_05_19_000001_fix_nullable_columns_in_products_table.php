<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // coverage es opcional (no todas las categorías son pinturas)
            $table->decimal('coverage', 8, 2)->nullable()->default(null)->change();

            // price puede no calcularse si no se llenan costo/margen
            $table->decimal('price', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('coverage', 8, 2)->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable(false)->default(null)->change();
        });
    }
};
