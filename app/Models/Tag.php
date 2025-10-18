<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
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

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
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

    // ❌ PROBLEMA: Búsqueda sin índices
    public function searchByName($term)
    {
        return $this->where('name', 'like', "%{$term}%")
            ->get();
    }

    // ❌ PROBLEMA: Método que carga relaciones innecesarias
    public function getTagWithAllPosts()
    {
        return $this->load('posts'); // Carga todos los posts sin filtros
    }

    // ❌ PROBLEMA: Método ineficiente para tags populares
    public function getPopularTags($limit = 10)
    {
        // ❌ PROBLEMA: Sin índices en la tabla pivot
        return $this->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->take($limit)
            ->get();
    }
}