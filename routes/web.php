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

// Rutas de Prueba de Rendimiento - SOLUCIONES
Route::get('/test/optimized/n-plus-one', function () {
    $start = microtime(true);
    
    // ✅ SOLUCIÓN: Eager loading para evitar N+1 queries
    $posts = \App\Models\Post::with(['user:id,name', 'category:id,name', 'tags:id,name'])
        ->select(['id', 'title', 'user_id', 'category_id'])
        ->take(100) // Limitar para demostración
        ->get();
    
    foreach ($posts as $post) {
        $post->user->name; // Sin query adicional
        $post->category->name; // Sin query adicional
        $post->tags->count(); // Sin query adicional
    }
    
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return response()->json([
        'message' => 'Eager loading ejecutado',
        'posts_count' => $posts->count(),
        'queries_executed' => '4 queries total (1 para posts + 3 para relaciones)',
        'optimization' => 'N+1 queries eliminadas',
        'execution_time_ms' => round($time, 2)
    ]);
});

Route::get('/test/optimized/fast-search', function () {
    $start = microtime(true);
    
    // ✅ SOLUCIÓN: Búsqueda optimizada con índices full-text
    $term = 'Laravel';
    $posts = \App\Models\Post::whereFullText(['title', 'content', 'excerpt'], $term)
        ->with(['user:id,name', 'category:id,name'])
        ->select(['id', 'title', 'slug', 'user_id', 'category_id'])
        ->take(50) // Limitar para demostración
        ->get();
    
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return response()->json([
        'message' => 'Búsqueda optimizada ejecutada',
        'term' => $term,
        'results_count' => $posts->count(),
        'note' => 'Con índices full-text y eager loading',
        'execution_time_ms' => round($time, 2)
    ]);
});

Route::get('/test/optimized/stats-with-cache', function () {
    $start = microtime(true);
    
    // ✅ SOLUCIÓN: Estadísticas con cache
    $stats = cache()->remember('test_stats', 300, function () {
        return [
            'posts' => \App\Models\Post::count(),
            'users' => \App\Models\User::count(),
            'comments' => \App\Models\Comment::count(),
            'likes' => \App\Models\Like::count(),
        ];
    });
    
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return response()->json([
        'message' => 'Estadísticas con cache',
        'stats' => $stats,
        'note' => 'Cache de 5 minutos',
        'execution_time_ms' => round($time, 2)
    ]);
});

Route::get('/test/optimized/popular-posts', function () {
    $start = microtime(true);
    
    // ✅ SOLUCIÓN: Posts populares optimizados
    $posts = cache()->remember('test_popular_posts', 600, function () {
        return \App\Models\Post::where('status', 'published')
            ->with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'likes_count', 'views_count'])
            ->orderBy('likes_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->take(10)
            ->get();
    });
    
    $end = microtime(true);
    $time = ($end - $start) * 1000;
    
    return response()->json([
        'message' => 'Posts populares optimizados',
        'posts_count' => $posts->count(),
        'note' => 'Con eager loading y cache',
        'execution_time_ms' => round($time, 2)
    ]);
});
