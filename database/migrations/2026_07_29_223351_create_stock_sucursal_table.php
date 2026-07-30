<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->integer('cantidad')->default(0);
            $table->integer('stock_minimo')->default(5);

            $table->unique(['producto_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_sucursal');
    }
};