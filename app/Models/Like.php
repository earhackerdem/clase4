<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'likeable_type',
        'likeable_id',
    ];

    // ❌ PROBLEMA: Relaciones sin optimización
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }

    // ❌ PROBLEMA: Métodos que causan N+1 queries
    public function getLikesWithUsers()
    {
        // ❌ PROBLEMA: Carga todos los likes sin eager loading
        return Like::all();
    }

    public function getLikesByUser($userId)
    {
        // ❌ PROBLEMA: Sin índice en user_id
        return Like::where('user_id', $userId)->get();
    }

    public function getLikesByPost($postId)
    {
        // ❌ PROBLEMA: Sin índices en likeable_type y likeable_id
        return Like::where('likeable_type', Post::class)
            ->where('likeable_id', $postId)
            ->get();
    }

    public function getLikesByComment($commentId)
    {
        // ❌ PROBLEMA: Sin índices en likeable_type y likeable_id
        return Like::where('likeable_type', Comment::class)
            ->where('likeable_id', $commentId)
            ->get();
    }

    // ❌ PROBLEMA: Método que causa múltiples queries
    public function getLikeWithUser($likeId)
    {
        $like = Like::find($likeId);
        
        // ❌ PROBLEMA: Queries separadas para cada relación
        $like->user;
        $like->likeable;
        
        return $like;
    }

    // ❌ PROBLEMA: Método ineficiente para likes recientes
    public function getRecentLikes($limit = 10)
    {
        // ❌ PROBLEMA: Sin índice en created_at
        return Like::orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    // ❌ PROBLEMA: Método que no usa paginación
    public function getAllLikes()
    {
        // ❌ PROBLEMA: Carga todos los likes de una vez
        return Like::all();
    }
}