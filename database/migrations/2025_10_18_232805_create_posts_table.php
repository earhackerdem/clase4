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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index(); // ✅ SOLUCIÓN: Índice para búsquedas
            $table->string('slug')->unique(); // ✅ SOLUCIÓN: Índice único
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index(); // ✅ SOLUCIÓN: Índice para filtros
            $table->timestamp('published_at')->nullable()->index(); // ✅ SOLUCIÓN: Índice para ordenamiento
            $table->foreignId('user_id')->index(); // ✅ SOLUCIÓN: Índice foreign key
            $table->foreignId('category_id')->index(); // ✅ SOLUCIÓN: Índice foreign key
            $table->integer('views_count')->default(0)->index(); // ✅ SOLUCIÓN: Índice para ordenamiento
            $table->integer('likes_count')->default(0)->index(); // ✅ SOLUCIÓN: Índice para ordenamiento
            $table->integer('comments_count')->default(0)->index(); // ✅ SOLUCIÓN: Índice para ordenamiento
            $table->json('meta_data')->nullable(); // SEO y metadatos
            $table->timestamps();
            
            // ✅ SOLUCIÓN: Índices para optimizar consultas
            $table->index(['status', 'published_at']); // Índice compuesto para posts publicados
            $table->index(['category_id', 'published_at']); // Índice compuesto para posts por categoría
            $table->index(['user_id', 'published_at']); // Índice compuesto para posts por usuario
            $table->index(['status', 'likes_count']); // Índice compuesto para posts populares
            $table->index(['status', 'views_count']); // Índice compuesto para posts más vistos
            
            // ✅ SOLUCIÓN: Índice full-text para búsquedas en contenido
            $table->fullText(['title', 'content', 'excerpt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};