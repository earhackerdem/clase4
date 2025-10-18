@extends('layouts.app')

@section('title', 'Posts - Problemas de Rendimiento')

@section('content')
<div class="space-y-6">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h2 class="text-lg font-semibold text-red-800 mb-2">🚨 Problemas de Rendimiento Detectados</h2>
        <ul class="text-red-700 space-y-1">
            <li>• N+1 queries masivas: cada post genera 5+ queries adicionales</li>
            <li>• Sin eager loading en relaciones</li>
            <li>• Carga de datos innecesarios</li>
            <li>• Sin paginación (carga todos los posts)</li>
        </ul>
    </div>

    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Todos los Posts</h1>
        <div class="flex space-x-4">
            <a href="{{ route('posts.popular') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Posts Populares
            </a>
            <a href="{{ route('posts.recent') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Posts Recientes
            </a>
        </div>
    </div>

    <!-- Información de Rendimiento -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <p class="text-yellow-800">
            <strong>⚠️ Advertencia:</strong> Esta página carga <strong>{{ $posts->count() }}</strong> posts con N+1 queries. 
            Cada post genera queries adicionales para usuario, categoría, tags, comentarios y likes.
        </p>
        <p class="text-yellow-700 text-sm mt-2">
            Total de queries ejecutadas: <strong>1 + ({{ $posts->count() }} × 5) = {{ 1 + ($posts->count() * 5) }}</strong>
        </p>
    </div>

    <!-- Lista de Posts -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($posts as $post)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($post->featured_image)
            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
            @endif
            
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">
                    <a href="{{ route('posts.show', $post->id) }}" class="hover:text-blue-600">
                        {{ $post->title }}
                    </a>
                </h2>
                
                <p class="text-gray-600 mb-4">{{ Str::limit($post->excerpt, 120) }}</p>
                
                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                    <div class="flex items-center space-x-2">
                        <span class="font-medium">{{ $post->user->name }}</span>
                        <span>•</span>
                        <span>{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                        {{ $post->category->name }}
                    </span>
                </div>
                
                <!-- Tags -->
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($post->tags->take(3) as $tag)
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                        #{{ $tag->name }}
                    </span>
                    @endforeach
                    @if($post->tags->count() > 3)
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                        +{{ $post->tags->count() - 3 }} más
                    </span>
                    @endif
                </div>
                
                <!-- Estadísticas -->
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <div class="flex space-x-4">
                        <span>👍 {{ $post->likes_count }}</span>
                        <span>👁️ {{ $post->views_count }}</span>
                        <span>💬 {{ $post->comments_count }}</span>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                        {{ ucfirst($post->status) }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Información de Rendimiento al Final -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-red-800 mb-4">📊 Análisis de Rendimiento</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-red-700 mb-2">Problemas Identificados:</h4>
                <ul class="text-red-600 space-y-1 text-sm">
                    <li>• N+1 queries en relaciones</li>
                    <li>• Sin eager loading</li>
                    <li>• Carga de todos los posts sin paginación</li>
                    <li>• Queries adicionales para tags</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-red-700 mb-2">Impacto en Rendimiento:</h4>
                <ul class="text-red-600 space-y-1 text-sm">
                    <li>• Tiempo de carga: Lento</li>
                    <li>• Queries ejecutadas: {{ 1 + ($posts->count() * 5) }}</li>
                    <li>• Uso de memoria: Alto</li>
                    <li>• Escalabilidad: Muy mala</li>
                </ul>
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
            <a href="{{ route('posts.search') }}?q=laravel" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center">
                Test Búsqueda Lenta
            </a>
        </div>
    </div>
</div>
@endsection
