<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'user_id',
        'category_id',
        'views_count',
        'likes_count',
        'comments_count',
        'meta_data',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meta_data' => 'array',
    ];

    // ❌ PROBLEMA: Relaciones sin optimización
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    // ❌ PROBLEMA: Métodos que causan N+1 queries masivas
    public function getPostsWithAllRelations()
    {
        // ❌ PROBLEMA: Carga todos los posts sin eager loading
        return Post::all();
    }

    public function getPublishedPosts()
    {
        // ❌ PROBLEMA: Sin índices en status y published_at
        return Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->get();
    }

    public function getPostsByCategory($categoryId)
    {
        // ❌ PROBLEMA: Sin índice en category_id
        return Post::where('category_id', $categoryId)->get();
    }

    public function getPostsByAuthor($userId)
    {
        // ❌ PROBLEMA: Sin índice en user_id
        return Post::where('user_id', $userId)->get();
    }

    // ❌ PROBLEMA: Búsqueda sin índices full-text
    public function searchPosts($term)
    {
        return Post::where('title', 'like', "%{$term}%")
            ->orWhere('content', 'like', "%{$term}%")
            ->orWhere('excerpt', 'like', "%{$term}%")
            ->get();
    }

    // ❌ PROBLEMA: Método que causa múltiples queries
    public function getPostWithStats($postId)
    {
        $post = Post::find($postId);
        
        // ❌ PROBLEMA: Queries separadas para cada relación
        $post->user;
        $post->category;
        $post->tags;
        $post->comments;
        $post->likes;
        $post->views;
        
        return $post;
    }

    // ❌ PROBLEMA: Método ineficiente para posts populares
    public function getPopularPosts($limit = 10)
    {
        // ❌ PROBLEMA: Sin índices en likes_count y views_count
        return Post::where('status', 'published')
            ->orderBy('likes_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->take($limit)
            ->get();
    }

    // ❌ PROBLEMA: Método que carga datos innecesarios
    public function getAllPostsWithFullContent()
    {
        // ❌ PROBLEMA: Carga todo el contenido sin select específico
        return Post::all();
    }

    // ❌ PROBLEMA: Método que no usa paginación
    public function getAllPosts()
    {
        // ❌ PROBLEMA: Carga todos los posts de una vez
        return Post::all();
    }
}