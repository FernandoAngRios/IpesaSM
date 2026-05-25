<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta_items', function (Blueprint $table) {
            $table->foreignId('base_tipo_id')
                ->nullable()
                ->after('product_presentation_id')
                ->constrained('base_tipos')
                ->nullOnDelete();

            // Snapshot del nombre de la base al momento de la venta
            $table->string('nombre_base', 50)->nullable()->after('base_tipo_id');
        });
    }

    public function down(): void
    {
        Schema::table('venta_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('base_tipo_id');
            $table->dropColumn('nombre_base');
        });
    }
};
