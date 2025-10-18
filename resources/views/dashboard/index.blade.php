@extends('layouts.app')

@section('title', 'Dashboard - Problemas de Rendimiento')

@section('content')
<div class="space-y-6">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h2 class="text-lg font-semibold text-red-800 mb-2">🚨 Problemas de Rendimiento Detectados</h2>
        <ul class="text-red-700 space-y-1">
            <li>• Múltiples queries separadas para estadísticas</li>
            <li>• N+1 queries en posts recientes y comentarios</li>
            <li>• Sin cache en datos que se consultan frecuentemente</li>
            <li>• Queries lentas sin índices en categorías y tags</li>
        </ul>
    </div>

    <h1 class="text-3xl font-bold text-gray-900">Dashboard - Estadísticas</h1>

    <!-- Estadísticas Generales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900">Total Usuarios</h3>
            <p class="text-3xl font-bold text-blue-600">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900">Total Posts</h3>
            <p class="text-3xl font-bold text-green-600">{{ number_format($totalPosts) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900">Total Comentarios</h3>
            <p class="text-3xl font-bold text-purple-600">{{ number_format($totalComments) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900">Total Likes</h3>
            <p class="text-3xl font-bold text-red-600">{{ number_format($totalLikes) }}</p>
        </div>
    </div>

    <!-- Estadísticas de Posts -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900">Posts Publicados</h3>
            <p class="text-2xl font-bold text-green-600">{{ number_format($publishedPosts) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900">Posts Borradores</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($draftPosts) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900">Total Vistas</h3>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalViews) }}</p>
        </div>
    </div>

    <!-- Posts Recientes -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold text-gray-900">Posts Recientes</h2>
            <p class="text-sm text-gray-600">❌ N+1 queries: cada post genera queries adicionales para usuario y categoría</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($recentPosts as $post)
                <div class="border-b pb-4 last:border-b-0">
                    <h3 class="font-semibold text-gray-900">{{ $post->title }}</h3>
                    <p class="text-sm text-gray-600">
                        Por <strong>{{ $post->user->name }}</strong> en 
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $post->category->name }}</span>
                    </p>
                    <p class="text-sm text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Comentarios Recientes -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold text-gray-900">Comentarios Recientes</h2>
            <p class="text-sm text-gray-600">❌ N+1 queries: cada comentario genera queries adicionales para usuario y post</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($recentComments as $comment)
                <div class="border-b pb-4 last:border-b-0">
                    <p class="text-gray-900">{{ Str::limit($comment->content, 100) }}</p>
                    <p class="text-sm text-gray-600">
                        Por <strong>{{ $comment->user->name }}</strong> en 
                        <strong>{{ $comment->post->title }}</strong>
                    </p>
                    <p class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Posts Populares -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold text-gray-900">Posts Populares</h2>
            <p class="text-sm text-gray-600">❌ Sin cache: se ejecutan queries lentas cada vez</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($popularPosts as $post)
                <div class="border-b pb-4 last:border-b-0">
                    <h3 class="font-semibold text-gray-900">{{ $post->title }}</h3>
                    <p class="text-sm text-gray-600">
                        Por <strong>{{ $post->user->name }}</strong> en 
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $post->category->name }}</span>
                    </p>
                    <div class="flex space-x-4 text-sm text-gray-500 mt-2">
                        <span>👍 {{ $post->likes_count }} likes</span>
                        <span>👁️ {{ $post->views_count }} vistas</span>
                        <span>💬 {{ $post->comments_count }} comentarios</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Enlaces de Prueba -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-yellow-800 mb-4">🧪 Pruebas de Rendimiento</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="/test/n-plus-one" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center">
                Test N+1 Queries
            </a>
            <a href="/test/slow-search" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center">
                Test Búsqueda Lenta
            </a>
            <a href="/test/stats-no-cache" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center">
                Test Stats Sin Cache
            </a>
            <a href="/test/popular-posts" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center">
                Test Posts Populares
            </a>
        </div>
    </div>
</div>
@endsection
