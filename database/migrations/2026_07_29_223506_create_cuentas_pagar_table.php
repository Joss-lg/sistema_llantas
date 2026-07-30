<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->string('concepto', 255)->nullable();
            $table->decimal('pago_ordinario', 12, 2)->default(0);
            $table->decimal('interes', 12, 2)->default(0);
            $table->decimal('monto_total', 12, 2)->default(0);
            $table->enum('tipo', ['cargo', 'abono'])->default('cargo');
            $table->date('fecha_movimiento');
            $table->string('factura', 100)->nullable();
            $table->text('notas')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_pagar');
    }
};