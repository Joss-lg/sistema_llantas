<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->string('nombre_cliente_temporal', 150)->nullable();
            $table->enum('tipo_precio', ['menudeo', 'mayoreo'])->default('menudeo');
            $table->boolean('requiere_factura')->default(false);
            $table->decimal('total', 12, 2);
            $table->dateTime('fecha')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};