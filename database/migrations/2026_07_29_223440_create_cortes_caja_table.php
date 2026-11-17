<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            
            // Dinero en caja
            $table->decimal('saldo_inicial', 12, 2);
            $table->decimal('saldo_final', 12, 2)->nullable(); // Se llena al cerrar la caja
            
            // Control de estado y tiempos
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->dateTime('fecha_apertura')->useCurrent();
            $table->dateTime('fecha_cierre')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_caja');
    }
};