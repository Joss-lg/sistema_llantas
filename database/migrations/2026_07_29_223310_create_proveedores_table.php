<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('rfc', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};