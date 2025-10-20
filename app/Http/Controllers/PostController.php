<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * ✅ SOLUCIÓN: Lista optimizada con eager loading y paginación
     */
    public function index()
    {
        // ✅ SOLUCIÓN: Eager loading para evitar N+1 queries
        $posts = Post::with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['id', 'title', 'slug', 'excerpt', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count', 'comments_count'])
            ->orderBy('published_at', 'desc')
            ->paginate(15);
        
        return view('posts.index', compact('posts'));
    }

    /**
     * ✅ SOLUCIÓN: Query optimizada con eager loading
     */
    public function show($id)
    {
        // ✅ SOLUCIÓN: Eager loading para evitar N+1 queries
        $post = Post::with([
            'user:id,name',
            'category:id,name',
            'tags:id,name',
            'comments' => function ($query) {
                $query->select(['id', 'post_id', 'user_id', 'content', 'created_at'])
                    ->with('user:id,name')
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc')
                    ->limit(20);
            }
        ])
        ->select(['id', 'title', 'slug', 'content', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count', 'comments_count'])
        ->findOrFail($id);
        
        return view('posts.show', compact('post'));
    }

    /**
     * ✅ SOLUCIÓN: Búsqueda optimizada con índices full-text
     */
    public function search(Request $request)
    {
        $term = $request->get('q');
        
        // ✅ SOLUCIÓN: Búsqueda con índices full-text y eager loading
        $posts = Post::whereFullText(['title', 'content', 'excerpt'], $term)
            ->with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['id', 'title', 'slug', 'excerpt', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->paginate(15);
        
        return view('posts.search', compact('posts', 'term'));
    }

    /**
     * ✅ SOLUCIÓN: Posts populares con cache y eager loading
     */
    public function popular()
    {
        // ✅ SOLUCIÓN: Cache para posts populares (1 hora)
        $posts = cache()->remember('popular_posts', 3600, function () {
            return Post::where('status', 'published')
                ->with(['user:id,name', 'category:id,name', 'tags:id,name'])
                ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
                ->orderBy('likes_count', 'desc')
                ->orderBy('views_count', 'desc')
                ->take(10)
                ->get();
        });
        
        return view('posts.popular', compact('posts'));
    }

    /**
     * ✅ SOLUCIÓN: Posts por categoría optimizados
     */
    public function byCategory($categoryId)
    {
        // ✅ SOLUCIÓN: Con índice en category_id y eager loading
        $posts = Post::where('category_id', $categoryId)
            ->with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->paginate(15);
        
        $category = Category::select(['id', 'name', 'slug'])->findOrFail($categoryId);
        
        return view('posts.category', compact('posts', 'category'));
    }

    /**
     * ✅ SOLUCIÓN: Posts por tag optimizados
     */
    public function byTag($tagId)
    {
        // ✅ SOLUCIÓN: Query optimizada con eager loading
        $tag = Tag::select(['id', 'name', 'slug'])->findOrFail($tagId);
        
        $posts = $tag->posts()
            ->with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['posts.id', 'posts.title', 'posts.slug', 'posts.user_id', 'posts.category_id', 'posts.published_at', 'posts.likes_count', 'posts.views_count'])
            ->orderBy('posts.published_at', 'desc')
            ->paginate(15);
        
        return view('posts.tag', compact('posts', 'tag'));
    }

    /**
     * ✅ SOLUCIÓN: Posts recientes optimizados
     */
    public function recent()
    {
        // ✅ SOLUCIÓN: Con índice en published_at y eager loading
        $posts = Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->take(20)
            ->get();
        
        return view('posts.recent', compact('posts'));
    }

    /**
     * ✅ SOLUCIÓN: Estadísticas optimizadas con cache
     */
    public function stats()
    {
        // ✅ SOLUCIÓN: Cache para estadísticas (30 minutos)
        $stats = cache()->remember('post_stats', 1800, function () {
            // ✅ SOLUCIÓN: Una sola query con agregaciones
            $aggregates = Post::selectRaw('
                COUNT(*) as total_posts,
                SUM(CASE WHEN status = "published" THEN 1 ELSE 0 END) as published_posts,
                SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft_posts,
                SUM(views_count) as total_views,
                SUM(likes_count) as total_likes,
                SUM(comments_count) as total_comments
            ')->first();
            
            // ✅ SOLUCIÓN: Posts populares con eager loading
            $popularPosts = Post::with(['user:id,name', 'category:id,name'])
                ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'likes_count', 'views_count'])
                ->orderBy('likes_count', 'desc')
                ->take(5)
                ->get();
            
            return [
                'aggregates' => $aggregates,
                'popular_posts' => $popularPosts
            ];
        });
        
        return view('posts.stats', [
            'totalPosts' => $stats['aggregates']->total_posts,
            'publishedPosts' => $stats['aggregates']->published_posts,
            'draftPosts' => $stats['aggregates']->draft_posts,
            'totalViews' => $stats['aggregates']->total_views,
            'totalLikes' => $stats['aggregates']->total_likes,
            'totalComments' => $stats['aggregates']->total_comments,
            'popularPosts' => $stats['popular_posts']
        ]);
    }

    /**
     * ✅ SOLUCIÓN: Posts con filtros optimizados
     */
    public function filtered(Request $request)
    {
        $query = Post::with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['id', 'title', 'slug', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count']);
        
        // ✅ SOLUCIÓN: Filtros con índices
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->has('author')) {
            $query->where('user_id', $request->author);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from')) {
            $query->where('published_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('published_at', '<=', $request->date_to);
        }
        
        // ✅ SOLUCIÓN: Con índices en campos de ordenamiento y paginación
        $posts = $query->orderBy('published_at', 'desc')->paginate(15);
        
        return view('posts.filtered', compact('posts'));
    }
}