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

    // ✅ SOLUCIÓN: Métodos optimizados con eager loading
    public function getPostsWithAllRelations()
    {
        // ✅ SOLUCIÓN: Eager loading para evitar N+1 queries
        return Post::with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->get();
    }

    public function getPublishedPosts()
    {
        // ✅ SOLUCIÓN: Con índices en status y published_at
        return Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->with(['user:id,name', 'category:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function getPostsByCategory($categoryId)
    {
        // ✅ SOLUCIÓN: Con índice en category_id y eager loading
        return Post::where('category_id', $categoryId)
            ->with(['user:id,name', 'category:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function getPostsByAuthor($userId)
    {
        // ✅ SOLUCIÓN: Con índice en user_id y eager loading
        return Post::where('user_id', $userId)
            ->with(['user:id,name', 'category:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    // ✅ SOLUCIÓN: Búsqueda con índices full-text
    public function searchPosts($term)
    {
        return Post::whereFullText(['title', 'content', 'excerpt'], $term)
            ->with(['user:id,name', 'category:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->get();
    }

    // ✅ SOLUCIÓN: Método optimizado con eager loading
    public function getPostWithStats($postId)
    {
        return Post::with([
            'user:id,name',
            'category:id,name',
            'tags:id,name',
            'comments' => function ($query) {
                $query->select(['id', 'post_id', 'user_id', 'content', 'created_at'])
                    ->with('user:id,name')
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc')
                    ->limit(10);
            }
        ])
        ->select(['id', 'title', 'slug', 'content', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count', 'comments_count'])
        ->find($postId);
    }

    // ✅ SOLUCIÓN: Método optimizado para posts populares
    public function getPopularPosts($limit = 10)
    {
        // ✅ SOLUCIÓN: Con índices en likes_count y views_count
        return Post::where('status', 'published')
            ->with(['user:id,name', 'category:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('likes_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->take($limit)
            ->get();
    }

    // ✅ SOLUCIÓN: Método optimizado con select específico
    public function getAllPostsWithFullContent()
    {
        return Post::with(['user:id,name', 'category:id,name'])
            ->select(['id', 'title', 'slug', 'excerpt', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    // ✅ SOLUCIÓN: Método con paginación
    public function getAllPosts($perPage = 15)
    {
        return Post::with(['user:id,name', 'category:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }
}