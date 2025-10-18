<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id'); // ❌ PROBLEMA: Sin índice foreign key
            $table->foreignId('user_id')->nullable(); // Usuario autenticado (opcional)
            $table->string('ip_address', 45); // ❌ PROBLEMA: Sin índice para análisis
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->timestamp('viewed_at'); // ❌ PROBLEMA: Sin índice para análisis temporal
            $table->timestamps();
            
            // ❌ PROBLEMA: Sin índices para optimizar consultas
            // No hay índices en post_id, user_id, ip_address, viewed_at
            // No hay índices compuestos para análisis de tráfico
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};