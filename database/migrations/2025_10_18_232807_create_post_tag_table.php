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
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->index(); // ✅ SOLUCIÓN: Índice foreign key
            $table->foreignId('tag_id')->index(); // ✅ SOLUCIÓN: Índice foreign key
            $table->timestamps();
            
            // ✅ SOLUCIÓN: Índices para optimizar consultas
            $table->unique(['post_id', 'tag_id']); // Índice único compuesto para evitar duplicados
            $table->index(['tag_id', 'post_id']); // Índice compuesto para consultas inversas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};