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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index(); // ✅ SOLUCIÓN: Índice para filtros
            $table->foreignId('user_id')->index(); // ✅ SOLUCIÓN: Índice foreign key
            $table->foreignId('post_id')->index(); // ✅ SOLUCIÓN: Índice foreign key
            $table->foreignId('parent_id')->nullable()->index(); // ✅ SOLUCIÓN: Índice para respuestas anidadas
            $table->integer('likes_count')->default(0)->index(); // ✅ SOLUCIÓN: Índice para ordenamiento
            $table->timestamps();
            
            // ✅ SOLUCIÓN: Índices para optimizar consultas
            $table->index(['post_id', 'status']); // Índice compuesto para comentarios por post
            $table->index(['user_id', 'status']); // Índice compuesto para comentarios por usuario
            $table->index(['parent_id', 'status']); // Índice compuesto para respuestas
            $table->index(['status', 'likes_count']); // Índice compuesto para comentarios populares
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};