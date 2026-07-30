<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->string('nombre', 100);
            $table->string('usuario', 50)->unique();
            $table->string('password', 255);
            $table->enum('rol', ['admin', 'operador'])->default('operador');
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};