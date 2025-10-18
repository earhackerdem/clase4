@extends('layouts.app')

@section('title', 'Búsqueda Global - Problemas de Rendimiento')

@section('content')
<div class="space-y-6">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h2 class="text-lg font-semibold text-red-800 mb-2">🚨 Problemas de Rendimiento Detectados</h2>
        <ul class="text-red-700 space-y-1">
            <li>• Búsqueda sin índices full-text</li>
            <li>• N+1 queries en resultados</li>
            <li>• Múltiples queries separadas para cada tipo</li>
            <li>• Sin optimización en LIKE queries</li>
        </ul>
    </div>

    <h1 class="text-3xl font-bold text-gray-900">Búsqueda Global</h1>

    <!-- Formulario de Búsqueda -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('search.global') }}" class="flex space-x-4">
            <input 
                type="text" 
                name="q" 
                value="{{ $term }}" 
                placeholder="Buscar en posts, categorías, tags y usuarios..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Buscar
            </button>
        </form>
        
        @if($term)
        <p class="text-sm text-gray-600 mt-2">
            ❌ Búsqueda sin índices full-text ejecutada para: <strong>"{{ $term }}"</strong>
        </p>
        @endif
    </div>

    @if($term)
    <!-- Resultados de Búsqueda -->
    <div class="space-y-8">
        <!-- Posts -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold text-gray-900">
                    Posts ({{ $posts->count() }})
                </h2>
                <p class="text-sm text-gray-600">❌ Búsqueda con LIKE sin índices + N+1 queries</p>
            </div>
            <div class="p-6">
                @if($posts->count() > 0)
                <div class="space-y-4">
                    @foreach($posts as $post)
                    <div class="border-b pb-4 last:border-b-0">
                        <h3 class="font-semibold text-gray-900">
                            <a href="{{ route('posts.show', $post->id) }}" class="hover:text-blue-600">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 text-sm mt-1">{{ Str::limit($post->excerpt, 150) }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center space-x-2 text-sm text-gray-500">
                                <span>Por <strong>{{ $post->user->name }}</strong></span>
                                <span>•</span>
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                {{ $post->category->name }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500">No se encontraron posts</p>
                @endif
            </div>
        </div>

        <!-- Categorías -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold text-gray-900">
                    Categorías ({{ $categories->count() }})
                </h2>
                <p class="text-sm text-gray-600">❌ Búsqueda con LIKE sin índices + N+1 queries</p>
            </div>
            <div class="p-6">
                @if($categories->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($categories as $category)
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ Str::limit($category->description, 100) }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm text-gray-500">
                                Por {{ $category->user->name }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ $category->posts->count() }} posts
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500">No se encontraron categorías</p>
                @endif
            </div>
        </div>

        <!-- Tags -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold text-gray-900">
                    Tags ({{ $tags->count() }})
                </h2>
                <p class="text-sm text-gray-600">❌ Búsqueda con LIKE sin índices + N+1 queries</p>
            </div>
            <div class="p-6">
                @if($tags->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                    <div class="border rounded-lg p-3">
                        <span class="font-semibold text-gray-900">#{{ $tag->name }}</span>
                        <p class="text-gray-600 text-sm mt-1">{{ Str::limit($tag->description, 80) }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm text-gray-500">
                                Por {{ $tag->user->name }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ $tag->posts->count() }} posts
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500">No se encontraron tags</p>
                @endif
            </div>
        </div>

        <!-- Usuarios -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold text-gray-900">
                    Usuarios ({{ $users->count() }})
                </h2>
                <p class="text-sm text-gray-600">❌ Búsqueda con LIKE sin índices</p>
            </div>
            <div class="p-6">
                @if($users->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($users as $user)
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-gray-600 text-sm">{{ $user->email }}</p>
                        <p class="text-sm text-gray-500 mt-2">
                            Miembro desde {{ $user->created_at->format('M Y') }}
                        </p>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500">No se encontraron usuarios</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Análisis de Rendimiento -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-red-800 mb-4">📊 Análisis de Rendimiento de Búsqueda</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-red-700 mb-2">Problemas Identificados:</h4>
                <ul class="text-red-600 space-y-1 text-sm">
                    <li>• Búsqueda con LIKE sin índices full-text</li>
                    <li>• N+1 queries en resultados</li>
                    <li>• Múltiples queries separadas</li>
                    <li>• Sin cache en resultados</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-red-700 mb-2">Impacto en Rendimiento:</h4>
                <ul class="text-red-600 space-y-1 text-sm">
                    <li>• Tiempo de búsqueda: Muy lento</li>
                    <li>• Queries ejecutadas: {{ 4 + ($posts->count() * 3) + ($categories->count() * 2) + ($tags->count() * 2) }}</li>
                    <li>• Uso de CPU: Alto</li>
                    <li>• Escalabilidad: Muy mala</li>
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Enlaces de Prueba -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-yellow-800 mb-4">🧪 Pruebas de Rendimiento</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="/test/slow-search?q=laravel" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center">
                Test Búsqueda Lenta
            </a>
            <a href="{{ route('search.advanced') }}" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center">
                Búsqueda Avanzada
            </a>
        </div>
    </div>
</div>
@endsection
