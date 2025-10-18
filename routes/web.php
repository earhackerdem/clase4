<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| Web Routes - PROBLEMAS DE RENDIMIENTO
|--------------------------------------------------------------------------
|
| Estas rutas demuestran problemas comunes de rendimiento en Laravel:
| - N+1 queries
| - Queries lentas sin índices
| - Falta de cache
| - Carga innecesaria de datos
|
*/

// Página principal
Route::get('/', function () {
    return view('welcome');
});

// ❌ PROBLEMA: Dashboard con múltiples queries lentas
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/detailed-stats', [DashboardController::class, 'detailedStats'])->name('dashboard.detailed-stats');
Route::get('/dashboard/performance-analysis', [DashboardController::class, 'performanceAnalysis'])->name('dashboard.performance-analysis');

// ❌ PROBLEMA: Posts con N+1 queries masivas
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
Route::get('/posts/category/{categoryId}', [PostController::class, 'byCategory'])->name('posts.category');
Route::get('/posts/tag/{tagId}', [PostController::class, 'byTag'])->name('posts.tag');
Route::get('/posts/popular', [PostController::class, 'popular'])->name('posts.popular');
Route::get('/posts/recent', [PostController::class, 'recent'])->name('posts.recent');
Route::get('/posts/stats', [PostController::class, 'stats'])->name('posts.stats');
Route::get('/posts/filtered', [PostController::class, 'filtered'])->name('posts.filtered');

// ❌ PROBLEMA: Búsquedas sin índices full-text
Route::get('/search', [SearchController::class, 'global'])->name('search.global');
Route::get('/search/advanced', [SearchController::class, 'advanced'])->name('search.advanced');
Route::get('/search/category/{categoryId}', [SearchController::class, 'byCategory'])->name('search.category');
Route::get('/search/tag/{tagId}', [SearchController::class, 'byTag'])->name('search.tag');
Route::get('/search/users', [SearchController::class, 'users'])->name('search.users');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// ❌ PROBLEMA: Búsqueda de posts sin optimización
Route::get('/posts/search', [PostController::class, 'search'])->name('posts.search');

/*
|--------------------------------------------------------------------------
| Rutas de Prueba de Rendimiento
|--------------------------------------------------------------------------
|
| Estas rutas están diseñadas para generar problemas de rendimiento
| y demostrar el impacto de las optimizaciones.
|
*/

// Rutas para probar N+1 queries
Route::get('/test/n-plus-one', function () {
    $start = microtime(true);
    $posts = \App\Models\Post::all();
    foreach ($posts as $post) {
        $post->user;
        $post->category;
        $post->tags;
    }
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return response()->json([
        'message' => 'N+1 queries ejecutadas',
        'posts_count' => $posts->count(),
        'execution_time_ms' => round($time, 2),
        'queries_count' => '1 + ' . ($posts->count() * 3) . ' = ' . (1 + ($posts->count() * 3))
    ]);
});

// Ruta para probar búsquedas lentas
Route::get('/test/slow-search', function () {
    $term = request('q', 'laravel');
    $start = microtime(true);
    
    $posts = \App\Models\Post::where('title', 'like', "%{$term}%")
        ->orWhere('content', 'like', "%{$term}%")
        ->get();
    
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return response()->json([
        'message' => 'Búsqueda lenta ejecutada',
        'term' => $term,
        'results_count' => $posts->count(),
        'execution_time_ms' => round($time, 2)
    ]);
});

// Ruta para probar estadísticas sin cache
Route::get('/test/stats-no-cache', function () {
    $start = microtime(true);
    
    $totalPosts = \App\Models\Post::count();
    $totalUsers = \App\Models\User::count();
    $totalComments = \App\Models\Comment::count();
    $totalLikes = \App\Models\Like::count();
    
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return response()->json([
        'message' => 'Estadísticas sin cache',
        'total_posts' => $totalPosts,
        'total_users' => $totalUsers,
        'total_comments' => $totalComments,
        'total_likes' => $totalLikes,
        'execution_time_ms' => round($time, 2)
    ]);
});

// Ruta para probar posts populares sin optimización
Route::get('/test/popular-posts', function () {
    $start = microtime(true);
    
    $posts = \App\Models\Post::where('status', 'published')
        ->orderBy('likes_count', 'desc')
        ->orderBy('views_count', 'desc')
        ->take(10)
        ->get();
    
    foreach ($posts as $post) {
        $post->user;
        $post->category;
    }
    
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return response()->json([
        'message' => 'Posts populares sin optimización',
        'posts_count' => $posts->count(),
        'execution_time_ms' => round($time, 2)
    ]);
});
