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
            $table->string('name'); // ❌ PROBLEMA: Sin índice para búsquedas
            $table->string('slug'); // ❌ PROBLEMA: Sin índice único
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6'); // Color hex para UI
            $table->foreignId('user_id'); // ❌ PROBLEMA: Sin índice foreign key
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // ❌ PROBLEMA: Sin índices para optimizar consultas
            // No hay índices en name, slug, user_id, is_active
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