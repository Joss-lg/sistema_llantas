<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('user_id')->constrained('users'); // Corregido al estándar de Laravel
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->string('nombre_cliente_temporal', 150)->nullable();
            $table->enum('tipo_precio', ['menudeo', 'mayoreo'])->default('menudeo');
            
            // Totales y Pagos
            $table->decimal('total', 12, 2);
            $table->decimal('pago_con', 12, 2)->nullable();
            $table->decimal('cambio', 12, 2)->nullable();
            
            $table->boolean('requiere_factura')->default(false);
            $table->dateTime('fecha')->useCurrent();
            
            // Agrega created_at y updated_at que requiere el método save() de Eloquent
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};