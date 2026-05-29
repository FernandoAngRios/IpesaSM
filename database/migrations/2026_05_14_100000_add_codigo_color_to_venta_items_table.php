<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta_items', function (Blueprint $table) {
            $table->string('codigo_color', 100)->nullable()->after('nombre_presentacion');
        });
    }

    public function down(): void
    {
        Schema::table('venta_items', function (Blueprint $table) {
            $table->dropColumn('codigo_color');
        });
    }
};
