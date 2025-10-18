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
            $table->foreignId('post_id'); // ❌ PROBLEMA: Sin índice foreign key
            $table->foreignId('tag_id'); // ❌ PROBLEMA: Sin índice foreign key
            $table->timestamps();
            
            // ❌ PROBLEMA: Sin índices para optimizar consultas
            // No hay índices en post_id, tag_id
            // No hay índice único compuesto para evitar duplicados
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