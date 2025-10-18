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
            $table->string('title'); // ❌ PROBLEMA: Sin índice para búsquedas
            $table->string('slug'); // ❌ PROBLEMA: Sin índice único
            $table->text('excerpt')->nullable(); // ❌ PROBLEMA: Sin índice para búsquedas
            $table->longText('content'); // ❌ PROBLEMA: Sin índice para búsquedas
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); // ❌ PROBLEMA: Sin índice
            $table->timestamp('published_at')->nullable(); // ❌ PROBLEMA: Sin índice para ordenamiento
            $table->foreignId('user_id'); // ❌ PROBLEMA: Sin índice foreign key
            $table->foreignId('category_id'); // ❌ PROBLEMA: Sin índice foreign key
            $table->integer('views_count')->default(0); // ❌ PROBLEMA: Sin índice para ordenamiento
            $table->integer('likes_count')->default(0); // ❌ PROBLEMA: Sin índice para ordenamiento
            $table->integer('comments_count')->default(0); // ❌ PROBLEMA: Sin índice para ordenamiento
            $table->json('meta_data')->nullable(); // SEO y metadatos
            $table->timestamps();
            
            // ❌ PROBLEMA: Sin índices para optimizar consultas
            // No hay índices en title, slug, status, published_at, user_id, category_id
            // No hay índices compuestos para consultas complejas
            // No hay índices full-text para búsquedas en contenido
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