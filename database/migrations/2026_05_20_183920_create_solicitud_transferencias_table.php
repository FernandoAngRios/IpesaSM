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
        Schema::create('solicitud_transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitante_sucursal_id')->constrained('sucursales');
            $table->foreignId('origen_sucursal_id')->constrained('sucursales');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('estado', ['pendiente', 'enviada', 'recibida', 'cancelada'])->default('pendiente');
            $table->text('notas_solicitud')->nullable();
            $table->text('notas_envio')->nullable();
            $table->text('notas_recepcion')->nullable();
            $table->foreignId('procesado_por')->nullable()->constrained('users');
            $table->timestamp('procesado_at')->nullable();
            $table->foreignId('recibido_por')->nullable()->constrained('users');
            $table->timestamp('recibido_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_transferencias');
    }
};
