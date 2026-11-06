<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cortes_caja', function (Blueprint $table) {
            // Efectivo que el cajero contó físicamente al cerrar el turno
            $table->decimal('efectivo_contado', 12, 2)->nullable()->after('saldo_final');

            // contado - esperado: positivo = sobrante, negativo = faltante
            $table->decimal('diferencia', 12, 2)->nullable()->after('efectivo_contado');
        });
    }

    public function down(): void
    {
        Schema::table('cortes_caja', function (Blueprint $table) {
            $table->dropColumn(['efectivo_contado', 'diferencia']);
        });
    }
};