<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            // Las transferencias existentes ya tienen el stock en destino, van como confirmadas
            $table->string('estado')->default('confirmada')->after('nota');
            $table->decimal('cantidad_recibida', 10, 3)->nullable()->after('estado');
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete()->after('cantidad_recibida');
            $table->timestamp('confirmado_at')->nullable()->after('confirmado_por');
            $table->string('nota_recepcion', 500)->nullable()->after('confirmado_at');
        });
    }

    public function down(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            $table->dropForeign(['confirmado_por']);
            $table->dropColumn(['estado', 'cantidad_recibida', 'confirmado_por', 'confirmado_at', 'nota_recepcion']);
        });
    }
};
