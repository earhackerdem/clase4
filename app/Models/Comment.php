<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'status',
        'user_id',
        'post_id',
        'parent_id',
        'likes_count',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // ❌ PROBLEMA: Relaciones sin optimización
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    // ❌ PROBLEMA: Métodos que causan N+1 queries
    public function getCommentsWithAuthors()
    {
        // ❌ PROBLEMA: Carga todos los comentarios sin eager loading
        return Comment::all();
    }

    public function getCommentsByPost($postId)
    {
        // ❌ PROBLEMA: Sin índice en post_id
        return Comment::where('post_id', $postId)->get();
    }

    public function getApprovedComments()
    {
        // ❌ PROBLEMA: Sin índice en status
        return Comment::where('status', 'approved')->get();
    }

    public function getCommentsByUser($userId)
    {
        // ❌ PROBLEMA: Sin índice en user_id
        return Comment::where('user_id', $userId)->get();
    }

    // ❌ PROBLEMA: Método que causa múltiples queries
    public function getCommentWithReplies($commentId)
    {
        $comment = Comment::find($commentId);
        
        // ❌ PROBLEMA: Queries separadas para cada relación
        $comment->user;
        $comment->post;
        $comment->replies;
        $comment->likes;
        
        return $comment;
    }

    // ❌ PROBLEMA: Búsqueda sin índices
    public function searchComments($term)
    {
        return Comment::where('content', 'like', "%{$term}%")
            ->get();
    }

    // ❌ PROBLEMA: Método ineficiente para comentarios populares
    public function getPopularComments($limit = 10)
    {
        // ❌ PROBLEMA: Sin índices en likes_count
        return Comment::where('status', 'approved')
            ->orderBy('likes_count', 'desc')
            ->take($limit)
            ->get();
    }

    // ❌ PROBLEMA: Método que no usa paginación
    public function getAllComments()
    {
        // ❌ PROBLEMA: Carga todos los comentarios de una vez
        return Comment::all();
    }
}