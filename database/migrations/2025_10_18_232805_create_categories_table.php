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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // ✅ SOLUCIÓN: Índice para búsquedas
            $table->string('slug')->unique(); // ✅ SOLUCIÓN: Índice único
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6'); // Color hex para UI
            $table->foreignId('user_id')->index(); // ✅ SOLUCIÓN: Índice foreign key
            $table->boolean('is_active')->default(true)->index(); // ✅ SOLUCIÓN: Índice para filtros
            $table->timestamps();
            
            // ✅ SOLUCIÓN: Índices para optimizar consultas
            $table->index(['is_active', 'name']); // Índice compuesto para filtros
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};