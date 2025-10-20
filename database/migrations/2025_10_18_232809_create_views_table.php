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
            $table->foreignId('post_id')->index(); // ✅ SOLUCIÓN: Índice foreign key
            $table->foreignId('user_id')->nullable()->index(); // ✅ SOLUCIÓN: Índice para usuarios autenticados
            $table->string('ip_address', 45)->index(); // ✅ SOLUCIÓN: Índice para análisis
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->timestamp('viewed_at')->index(); // ✅ SOLUCIÓN: Índice para análisis temporal
            $table->timestamps();
            
            // ✅ SOLUCIÓN: Índices para optimizar consultas
            $table->index(['post_id', 'viewed_at']); // Índice compuesto para análisis por post
            $table->index(['ip_address', 'viewed_at']); // Índice compuesto para análisis por IP
            $table->index(['user_id', 'viewed_at']); // Índice compuesto para análisis por usuario
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