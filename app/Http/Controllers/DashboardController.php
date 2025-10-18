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
     * ❌ PROBLEMA: Dashboard con múltiples queries lentas
     * Obtiene estadísticas del dashboard sin optimización
     */
    public function index()
    {
        // ❌ PROBLEMA: Múltiples queries separadas para estadísticas
        $totalUsers = User::count();
        $totalPosts = Post::count();
        $totalCategories = Category::count();
        $totalTags = Tag::count();
        $totalComments = Comment::count();
        $totalLikes = Like::count();
        $totalViews = View::count();
        
        // ❌ PROBLEMA: Queries adicionales para datos específicos
        $publishedPosts = Post::where('status', 'published')->count();
        $draftPosts = Post::where('status', 'draft')->count();
        $approvedComments = Comment::where('status', 'approved')->count();
        $pendingComments = Comment::where('status', 'pending')->count();
        
        // ❌ PROBLEMA: Posts recientes sin eager loading
        $recentPosts = Post::orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        foreach ($recentPosts as $post) {
            $post->user;
            $post->category;
        }
        
        // ❌ PROBLEMA: Comentarios recientes sin eager loading
        $recentComments = Comment::orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        foreach ($recentComments as $comment) {
            $comment->user;
            $comment->post;
        }
        
        // ❌ PROBLEMA: Posts populares sin optimización
        $popularPosts = Post::orderBy('likes_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();
        
        foreach ($popularPosts as $post) {
            $post->user;
            $post->category;
        }
        
        // ❌ PROBLEMA: Categorías más usadas sin optimización
        $categories = Category::all();
        $categoryStats = [];
        foreach ($categories as $category) {
            $categoryStats[] = [
                'category' => $category,
                'posts_count' => $category->posts()->count(), // Query adicional por categoría
            ];
        }
        
        // ❌ PROBLEMA: Tags más usados sin optimización
        $tags = Tag::all();
        $tagStats = [];
        foreach ($tags as $tag) {
            $tagStats[] = [
                'tag' => $tag,
                'posts_count' => $tag->posts()->count(), // Query adicional por tag
            ];
        }
        
        // ❌ PROBLEMA: Estadísticas de usuarios sin optimización
        $users = User::all();
        $userStats = [];
        foreach ($users->take(10) as $user) {
            $userStats[] = [
                'user' => $user,
                'posts_count' => $user->posts()->count(), // Query adicional por usuario
                'comments_count' => $user->comments()->count(), // Query adicional por usuario
            ];
        }
        
        return view('dashboard.index', compact(
            'totalUsers',
            'totalPosts',
            'totalCategories',
            'totalTags',
            'totalComments',
            'totalLikes',
            'totalViews',
            'publishedPosts',
            'draftPosts',
            'approvedComments',
            'pendingComments',
            'recentPosts',
            'recentComments',
            'popularPosts',
            'categoryStats',
            'tagStats',
            'userStats'
        ));
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