<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corte_caja_id')->constrained('cortes_caja')->cascadeOnDelete();
            
            $table->enum('tipo', ['ingreso', 'egreso']); // Ingreso (Anticipo) o Egreso (Gasto)
            $table->string('concepto', 255); // Ej. "Pago de garrafón de agua"
            $table->decimal('monto', 12, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};