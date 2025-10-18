<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class View extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referer',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    // ❌ PROBLEMA: Relaciones sin optimización
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ❌ PROBLEMA: Métodos que causan N+1 queries
    public function getViewsWithPosts()
    {
        // ❌ PROBLEMA: Carga todas las vistas sin eager loading
        return View::all();
    }

    public function getViewsByPost($postId)
    {
        // ❌ PROBLEMA: Sin índice en post_id
        return View::where('post_id', $postId)->get();
    }

    public function getViewsByUser($userId)
    {
        // ❌ PROBLEMA: Sin índice en user_id
        return View::where('user_id', $userId)->get();
    }

    public function getViewsByIp($ipAddress)
    {
        // ❌ PROBLEMA: Sin índice en ip_address
        return View::where('ip_address', $ipAddress)->get();
    }

    // ❌ PROBLEMA: Método que causa múltiples queries
    public function getViewWithPost($viewId)
    {
        $view = View::find($viewId);
        
        // ❌ PROBLEMA: Queries separadas para cada relación
        $view->post;
        $view->user;
        
        return $view;
    }

    // ❌ PROBLEMA: Método ineficiente para vistas recientes
    public function getRecentViews($limit = 10)
    {
        // ❌ PROBLEMA: Sin índice en viewed_at
        return View::orderBy('viewed_at', 'desc')
            ->take($limit)
            ->get();
    }

    // ❌ PROBLEMA: Método que no usa paginación
    public function getAllViews()
    {
        // ❌ PROBLEMA: Carga todas las vistas de una vez
        return View::all();
    }

    // ❌ PROBLEMA: Método ineficiente para estadísticas
    public function getViewsStats($postId)
    {
        // ❌ PROBLEMA: Múltiples queries separadas
        $totalViews = View::where('post_id', $postId)->count();
        $uniqueViews = View::where('post_id', $postId)->distinct('ip_address')->count();
        $recentViews = View::where('post_id', $postId)
            ->where('viewed_at', '>=', now()->subDays(7))
            ->count();
        
        return [
            'total_views' => $totalViews,
            'unique_views' => $uniqueViews,
            'recent_views' => $recentViews,
        ];
    }
}