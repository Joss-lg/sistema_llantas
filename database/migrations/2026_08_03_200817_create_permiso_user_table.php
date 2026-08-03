<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permiso_user', function (Blueprint $table) {
            $table->id();
            
            // Foreign key para el usuario
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Foreign key para el permiso
            $table->foreignId('permiso_id')->constrained('permisos')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_user');
    }
};