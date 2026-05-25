<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            $table->enum('tipo', ['proveedor', 'sucursal'])->default('sucursal')->after('id');
            $table->string('proveedor_nombre')->nullable()->after('tipo');
            $table->unsignedBigInteger('origen_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'proveedor_nombre']);
            $table->unsignedBigInteger('origen_id')->nullable(false)->change();
        });
    }
};
