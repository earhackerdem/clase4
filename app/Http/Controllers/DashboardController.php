<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Like;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * ✅ SOLUCIÓN: Dashboard optimizado con cache y eager loading
     */
    public function index()
    {
        // ✅ SOLUCIÓN: Cache para estadísticas del dashboard (15 minutos)
        $dashboardData = cache()->remember('dashboard_stats', 900, function () {
            // ✅ SOLUCIÓN: Una sola query con agregaciones
            $stats = DB::select('
                SELECT 
                    (SELECT COUNT(*) FROM posts) as total_posts,
                    (SELECT COUNT(*) FROM users) as total_users,
                    (SELECT COUNT(*) FROM categories) as total_categories,
                    (SELECT COUNT(*) FROM tags) as total_tags,
                    (SELECT COUNT(*) FROM comments) as total_comments,
                    (SELECT COUNT(*) FROM likes) as total_likes,
                    (SELECT COUNT(*) FROM views) as total_views,
                    (SELECT COUNT(*) FROM posts WHERE status = "published") as published_posts,
                    (SELECT COUNT(*) FROM posts WHERE status = "draft") as draft_posts,
                    (SELECT COUNT(*) FROM comments WHERE status = "approved") as approved_comments,
                    (SELECT COUNT(*) FROM comments WHERE status = "pending") as pending_comments
            ')[0];
            
            // ✅ SOLUCIÓN: Posts recientes con eager loading
            $recentPosts = Post::with(['user:id,name', 'category:id,name'])
                ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            
            // ✅ SOLUCIÓN: Comentarios recientes con eager loading
            $recentComments = Comment::with(['user:id,name', 'post:id,title'])
                ->select(['id', 'content', 'user_id', 'post_id', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            
            // ✅ SOLUCIÓN: Posts populares con eager loading
            $popularPosts = Post::with(['user:id,name', 'category:id,name'])
                ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'likes_count', 'views_count'])
                ->orderBy('likes_count', 'desc')
                ->orderBy('views_count', 'desc')
                ->take(5)
                ->get();
            
            // ✅ SOLUCIÓN: Categorías más usadas con eager loading
            $categoryStats = Category::withCount('posts')
                ->orderBy('posts_count', 'desc')
                ->take(10)
                ->get();
            
            // ✅ SOLUCIÓN: Tags más usados con eager loading
            $tagStats = Tag::withCount('posts')
                ->orderBy('posts_count', 'desc')
                ->take(10)
                ->get();
            
            // ✅ SOLUCIÓN: Usuarios más activos con eager loading
            $userStats = User::withCount(['posts', 'comments'])
                ->orderBy('posts_count', 'desc')
                ->take(10)
                ->get();
            
            return [
                'stats' => $stats,
                'recent_posts' => $recentPosts,
                'recent_comments' => $recentComments,
                'popular_posts' => $popularPosts,
                'category_stats' => $categoryStats,
                'tag_stats' => $tagStats,
                'user_stats' => $userStats
            ];
        });
        
        return view('dashboard.index', [
            'totalUsers' => $dashboardData['stats']->total_users,
            'totalPosts' => $dashboardData['stats']->total_posts,
            'totalCategories' => $dashboardData['stats']->total_categories,
            'totalTags' => $dashboardData['stats']->total_tags,
            'totalComments' => $dashboardData['stats']->total_comments,
            'totalLikes' => $dashboardData['stats']->total_likes,
            'totalViews' => $dashboardData['stats']->total_views,
            'publishedPosts' => $dashboardData['stats']->published_posts,
            'draftPosts' => $dashboardData['stats']->draft_posts,
            'approvedComments' => $dashboardData['stats']->approved_comments,
            'pendingComments' => $dashboardData['stats']->pending_comments,
            'recentPosts' => $dashboardData['recent_posts'],
            'recentComments' => $dashboardData['recent_comments'],
            'popularPosts' => $dashboardData['popular_posts'],
            'categoryStats' => $dashboardData['category_stats'],
            'tagStats' => $dashboardData['tag_stats'],
            'userStats' => $dashboardData['user_stats']
        ]);
    }

    /**
     * ❌ PROBLEMA: Estadísticas detalladas sin optimización
     * Obtiene estadísticas detalladas con múltiples queries
     */
    public function detailedStats()
    {
        // ❌ PROBLEMA: Estadísticas por mes sin optimización
        $monthlyStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('Y-m'),
                'posts' => Post::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'comments' => Comment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'likes' => Like::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'views' => View::whereYear('viewed_at', $date->year)
                    ->whereMonth('viewed_at', $date->month)
                    ->count(),
            ];
        }
        
        // ❌ PROBLEMA: Top categorías sin optimización
        $topCategories = Category::all();
        $categoryRanking = [];
        foreach ($topCategories as $category) {
            $categoryRanking[] = [
                'category' => $category,
                'posts_count' => $category->posts()->count(),
                'total_views' => $category->posts()->sum('views_count'),
                'total_likes' => $category->posts()->sum('likes_count'),
            ];
        }
        
        // ❌ PROBLEMA: Top tags sin optimización
        $topTags = Tag::all();
        $tagRanking = [];
        foreach ($topTags as $tag) {
            $tagRanking[] = [
                'tag' => $tag,
                'posts_count' => $tag->posts()->count(),
            ];
        }
        
        // ❌ PROBLEMA: Top usuarios sin optimización
        $topUsers = User::all();
        $userRanking = [];
        foreach ($topUsers as $user) {
            $userRanking[] = [
                'user' => $user,
                'posts_count' => $user->posts()->count(),
                'comments_count' => $user->comments()->count(),
                'total_likes' => $user->likes()->count(),
            ];
        }
        
        return view('dashboard.detailed-stats', compact(
            'monthlyStats',
            'categoryRanking',
            'tagRanking',
            'userRanking'
        ));
    }

    /**
     * ❌ PROBLEMA: Análisis de rendimiento sin optimización
     * Obtiene análisis de rendimiento con queries lentas
     */
    public function performanceAnalysis()
    {
        // ❌ PROBLEMA: Posts con más comentarios sin optimización
        $postsWithMostComments = Post::orderBy('comments_count', 'desc')
            ->take(10)
            ->get();
        
        foreach ($postsWithMostComments as $post) {
            $post->user;
            $post->category;
        }
        
        // ❌ PROBLEMA: Posts con más likes sin optimización
        $postsWithMostLikes = Post::orderBy('likes_count', 'desc')
            ->take(10)
            ->get();
        
        foreach ($postsWithMostLikes as $post) {
            $post->user;
            $post->category;
        }
        
        // ❌ PROBLEMA: Posts con más vistas sin optimización
        $postsWithMostViews = Post::orderBy('views_count', 'desc')
            ->take(10)
            ->get();
        
        foreach ($postsWithMostViews as $post) {
            $post->user;
            $post->category;
        }
        
        // ❌ PROBLEMA: Usuarios más activos sin optimización
        $activeUsers = User::all();
        $userActivity = [];
        foreach ($activeUsers as $user) {
            $userActivity[] = [
                'user' => $user,
                'posts_count' => $user->posts()->count(),
                'comments_count' => $user->comments()->count(),
                'likes_count' => $user->likes()->count(),
                'total_activity' => $user->posts()->count() + $user->comments()->count() + $user->likes()->count(),
            ];
        }
        
        return view('dashboard.performance-analysis', compact(
            'postsWithMostComments',
            'postsWithMostLikes',
            'postsWithMostViews',
            'userActivity'
        ));
    }
}