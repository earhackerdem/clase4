<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * ✅ SOLUCIÓN: Búsqueda global optimizada con índices y eager loading
     * Busca en posts, categorías, tags y usuarios con optimizaciones
     */
    public function global(Request $request)
    {
        $term = $request->get('q');
        
        if (empty($term)) {
            return view('search.global', [
                'posts' => collect(),
                'categories' => collect(),
                'tags' => collect(),
                'users' => collect(),
                'term' => $term
            ]);
        }
        
        // ✅ SOLUCIÓN: Búsqueda en posts con índices full-text y eager loading
        $posts = Post::whereFullText(['title', 'content', 'excerpt'], $term)
            ->with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->select(['id', 'title', 'slug', 'excerpt', 'user_id', 'category_id', 'published_at', 'likes_count', 'views_count'])
            ->orderBy('published_at', 'desc')
            ->take(20)
            ->get();
        
        // ✅ SOLUCIÓN: Búsqueda en categorías con índices y eager loading
        $categories = Category::where('name', 'like', "%{$term}%")
            ->with(['user:id,name'])
            ->select(['id', 'name', 'slug', 'color', 'user_id'])
            ->take(10)
            ->get();
        
        // ✅ SOLUCIÓN: Búsqueda en tags con índices y eager loading
        $tags = Tag::where('name', 'like', "%{$term}%")
            ->with(['user:id,name'])
            ->select(['id', 'name', 'slug', 'color', 'user_id'])
            ->take(10)
            ->get();
        
        // ✅ SOLUCIÓN: Búsqueda en usuarios con índices
        $users = User::where('name', 'like', "%{$term}%")
            ->select(['id', 'name', 'email', 'created_at'])
            ->take(10)
            ->get();
        
        return view('search.global', compact('posts', 'categories', 'tags', 'users', 'term'));
    }

    /**
     * ❌ PROBLEMA: Búsqueda avanzada sin optimización
     * Búsqueda con múltiples filtros sin índices
     */
    public function advanced(Request $request)
    {
        $term = $request->get('q');
        $category = $request->get('category');
        $tag = $request->get('tag');
        $author = $request->get('author');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = Post::query();
        
        // ❌ PROBLEMA: Búsqueda de texto sin índices full-text
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%")
                  ->orWhere('excerpt', 'like', "%{$term}%");
            });
        }
        
        // ❌ PROBLEMA: Filtros sin índices
        if ($category) {
            $query->where('category_id', $category);
        }
        
        if ($author) {
            $query->where('user_id', $author);
        }
        
        if ($dateFrom) {
            $query->where('published_at', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->where('published_at', '<=', $dateTo);
        }
        
        // ❌ PROBLEMA: Filtro por tag sin optimización
        if ($tag) {
            $tagModel = Tag::find($tag);
            if ($tagModel) {
                $postIds = $tagModel->posts()->pluck('posts.id');
                $query->whereIn('id', $postIds);
            }
        }
        
        // ❌ PROBLEMA: Sin índices en campos de ordenamiento
        $posts = $query->orderBy('published_at', 'desc')->get();
        
        // ❌ PROBLEMA: N+1 queries para cada post
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        // ❌ PROBLEMA: Cargar opciones de filtro sin optimización
        $categories = Category::all();
        $tags = Tag::all();
        $users = User::all();
        
        return view('search.advanced', compact(
            'posts',
            'categories',
            'tags',
            'users',
            'term',
            'category',
            'tag',
            'author',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * ❌ PROBLEMA: Búsqueda por categoría sin optimización
     * Busca posts en una categoría específica
     */
    public function byCategory(Request $request, $categoryId)
    {
        $term = $request->get('q');
        
        // ❌ PROBLEMA: Sin índice en category_id
        $query = Post::where('category_id', $categoryId);
        
        if ($term) {
            // ❌ PROBLEMA: Búsqueda sin índices full-text
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%")
                  ->orWhere('excerpt', 'like', "%{$term}%");
            });
        }
        
        // ❌ PROBLEMA: Sin índices en published_at
        $posts = $query->orderBy('published_at', 'desc')->get();
        
        // ❌ PROBLEMA: N+1 queries para cada post
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        $category = Category::find($categoryId);
        
        return view('search.category', compact('posts', 'category', 'term'));
    }

    /**
     * ❌ PROBLEMA: Búsqueda por tag sin optimización
     * Busca posts con un tag específico
     */
    public function byTag(Request $request, $tagId)
    {
        $term = $request->get('q');
        
        // ❌ PROBLEMA: Query lenta en tabla pivot
        $tag = Tag::find($tagId);
        $query = $tag->posts();
        
        if ($term) {
            // ❌ PROBLEMA: Búsqueda sin índices full-text
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%")
                  ->orWhere('excerpt', 'like', "%{$term}%");
            });
        }
        
        // ❌ PROBLEMA: Sin índices en published_at
        $posts = $query->orderBy('published_at', 'desc')->get();
        
        // ❌ PROBLEMA: N+1 queries para cada post
        foreach ($posts as $post) {
            $post->user;
            $post->category;
            $post->tags;
        }
        
        return view('search.tag', compact('posts', 'tag', 'term'));
    }

    /**
     * ❌ PROBLEMA: Búsqueda de usuarios sin optimización
     * Busca usuarios con sus estadísticas
     */
    public function users(Request $request)
    {
        $term = $request->get('q');
        
        // ❌ PROBLEMA: Búsqueda sin índices
        $users = User::where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->get();
        
        // ❌ PROBLEMA: N+1 queries para cada usuario
        foreach ($users as $user) {
            $user->posts;
            $user->comments;
            $user->likes;
        }
        
        return view('search.users', compact('users', 'term'));
    }

    /**
     * ❌ PROBLEMA: Sugerencias de búsqueda sin optimización
     * Obtiene sugerencias de búsqueda con queries lentas
     */
    public function suggestions(Request $request)
    {
        $term = $request->get('q');
        
        if (strlen($term) < 2) {
            return response()->json([]);
        }
        
        // ❌ PROBLEMA: Múltiples queries separadas para sugerencias
        $postTitles = Post::where('title', 'like', "%{$term}%")
            ->pluck('title')
            ->take(5);
        
        $categoryNames = Category::where('name', 'like', "%{$term}%")
            ->pluck('name')
            ->take(5);
        
        $tagNames = Tag::where('name', 'like', "%{$term}%")
            ->pluck('name')
            ->take(5);
        
        $userNames = User::where('name', 'like', "%{$term}%")
            ->pluck('name')
            ->take(5);
        
        $suggestions = collect()
            ->merge($postTitles)
            ->merge($categoryNames)
            ->merge($tagNames)
            ->merge($userNames)
            ->unique()
            ->take(10);
        
        return response()->json($suggestions);
    }
}