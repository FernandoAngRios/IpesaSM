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
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->foreignId('devolucion_id')->nullable()->after('venta_id')
                  ->constrained('devoluciones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Devolucion::class);
            $table->dropColumn('devolucion_id');
        });
    }
};
