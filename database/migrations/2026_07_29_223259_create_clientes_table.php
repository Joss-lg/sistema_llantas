<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('rfc', 20)->nullable();
            $table->string('razon_social', 150)->nullable();
            $table->string('uso_cfdi', 5)->nullable();
            $table->string('cp', 10)->nullable();
            $table->string('regimen_fiscal', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};