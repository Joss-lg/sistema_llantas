<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna 'metodo_pago' a la tabla ventas.
     *
     * - metodo_pago: 'Efectivo', 'Tarjeta' o 'Transferencia' (texto)
     * - pago_con:    se queda como está (número) = dinero que entregó el cliente
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('metodo_pago', 20)->default('Efectivo')->after('total');
            $table->index('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex(['metodo_pago']);
            $table->dropColumn('metodo_pago');
        });
    }
};