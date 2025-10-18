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
     * ❌ PROBLEMA: N+1 queries masivas
     * Lista todos los posts con relaciones sin eager loading
     */
    public function index()
    {
        // ❌ PROBLEMA: Carga todos los posts sin eager loading
        $posts = Post::all();
        
        // ❌ PROBLEMA: N+1 queries - cada post genera queries adicionales
        foreach ($posts as $post) {
            $post->user; // Query adicional por cada post
            $post->category; // Query adicional por cada post
            $post->tags; // Query adicional por cada post
            $post->comments; // Query adicional por cada post
            $post->likes; // Query adicional por cada post
        }
        
        return view('posts.index', compact('posts'));
    }

    /**
     * ❌ PROBLEMA: Query lenta sin índices
     * Muestra un post específico con todas las relaciones
     */
    public function show($id)
    {
        // ❌ PROBLEMA: Carga el post sin eager loading
        $post = Post::find($id);
        
        // ❌ PROBLEMA: Queries separadas para cada relación
        $post->user;
        $post->category;
        $post->tags;
        $post->comments;
        $post->likes;
        $post->views;
        
        // ❌ PROBLEMA: Query adicional para comentarios con usuarios
        $comments = $post->comments;
        foreach ($comments as $comment) {
            $comment->user; // Query adicional por cada comentario
        }
        
        return view('posts.show', compact('post', 'comments'));
    }

    /**
     * ❌ PROBLEMA: Búsqueda sin índices full-text
     * Busca posts por término sin optimización
     */
    public function search(Request $request)
    {
        $term = $request->get('q');
        
        // ❌ PROBLEMA: Búsqueda sin índices full-text
        $posts = Post::where('title', 'like', "%{$term}%")
            ->orWhere('content', 'like', "%{$term}%")
            ->orWhere('excerpt', 'like', "%{$term}%")
            ->get();
        
        // ❌ PROBLEMA: N+1 queries para cada resultado
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        return view('posts.search', compact('posts', 'term'));
    }

    /**
     * ❌ PROBLEMA: Posts populares sin cache
     * Obtiene posts populares con queries lentas
     */
    public function popular()
    {
        // ❌ PROBLEMA: Query lenta sin índices en likes_count y views_count
        $posts = Post::where('status', 'published')
            ->orderBy('likes_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->take(10)
            ->get();
        
        // ❌ PROBLEMA: N+1 queries para cada post
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        return view('posts.popular', compact('posts'));
    }

    /**
     * ❌ PROBLEMA: Posts por categoría sin optimización
     * Filtra posts por categoría con N+1 queries
     */
    public function byCategory($categoryId)
    {
        // ❌ PROBLEMA: Sin índice en category_id
        $posts = Post::where('category_id', $categoryId)->get();
        
        // ❌ PROBLEMA: N+1 queries para cada post
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        $category = Category::find($categoryId);
        
        return view('posts.category', compact('posts', 'category'));
    }

    /**
     * ❌ PROBLEMA: Posts por tag sin optimización
     * Filtra posts por tag con N+1 queries
     */
    public function byTag($tagId)
    {
        // ❌ PROBLEMA: Query lenta en tabla pivot sin índices
        $tag = Tag::find($tagId);
        $posts = $tag->posts;
        
        // ❌ PROBLEMA: N+1 queries para cada post
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        return view('posts.tag', compact('posts', 'tag'));
    }

    /**
     * ❌ PROBLEMA: Posts recientes sin optimización
     * Obtiene posts recientes con N+1 queries
     */
    public function recent()
    {
        // ❌ PROBLEMA: Sin índice en published_at
        $posts = Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->take(20)
            ->get();
        
        // ❌ PROBLEMA: N+1 queries para cada post
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        return view('posts.recent', compact('posts'));
    }

    /**
     * ❌ PROBLEMA: Estadísticas sin optimización
     * Obtiene estadísticas con múltiples queries separadas
     */
    public function stats()
    {
        // ❌ PROBLEMA: Múltiples queries separadas
        $totalPosts = Post::count();
        $publishedPosts = Post::where('status', 'published')->count();
        $draftPosts = Post::where('status', 'draft')->count();
        $totalViews = Post::sum('views_count');
        $totalLikes = Post::sum('likes_count');
        $totalComments = Post::sum('comments_count');
        
        // ❌ PROBLEMA: Query adicional para posts más populares
        $popularPosts = Post::orderBy('likes_count', 'desc')
            ->take(5)
            ->get();
        
        foreach ($popularPosts as $post) {
            $post->user;
            $post->category;
        }
        
        return view('posts.stats', compact(
            'totalPosts',
            'publishedPosts',
            'draftPosts',
            'totalViews',
            'totalLikes',
            'totalComments',
            'popularPosts'
        ));
    }

    /**
     * ❌ PROBLEMA: Posts con filtros complejos sin optimización
     * Filtra posts con múltiples criterios
     */
    public function filtered(Request $request)
    {
        $query = Post::query();
        
        // ❌ PROBLEMA: Filtros sin índices
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
        
        // ❌ PROBLEMA: Sin índices en campos de ordenamiento
        $posts = $query->orderBy('published_at', 'desc')->get();
        
        // ❌ PROBLEMA: N+1 queries para cada post
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        return view('posts.filtered', compact('posts'));
    }
}