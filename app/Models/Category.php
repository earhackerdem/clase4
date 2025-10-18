<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ❌ PROBLEMA: Relaciones sin optimización
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // ❌ PROBLEMA: Métodos que causan N+1 queries
    public function getPostsWithAuthors()
    {
        // ❌ PROBLEMA: Carga todos los posts sin eager loading
        return $this->posts()->get();
    }

    public function getPostsCount()
    {
        // ❌ PROBLEMA: Query separada para contar
        return $this->posts()->count();
    }

    public function getActivePosts()
    {
        // ❌ PROBLEMA: Sin índices en status y published_at
        return $this->posts()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->get();
    }

    // ❌ PROBLEMA: Búsqueda sin índices
    public function searchByName($term)
    {
        return $this->where('name', 'like', "%{$term}%")
            ->get();
    }

    // ❌ PROBLEMA: Método que carga relaciones innecesarias
    public function getCategoryWithAllPosts()
    {
        return $this->load('posts'); // Carga todos los posts sin filtros
    }
}