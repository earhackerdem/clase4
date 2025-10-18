<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Blog de Optimización - Problemas de Rendimiento')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .problem-badge {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                        Blog de Optimización
                    </a>
                    <span class="problem-badge">PROBLEMAS</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="{{ route('posts.index') }}" class="text-gray-600 hover:text-gray-900">Posts</a>
                    <a href="{{ route('search.global') }}" class="text-gray-600 hover:text-gray-900">Búsqueda</a>
                    <a href="/test/n-plus-one" class="text-red-600 hover:text-red-900">Test N+1</a>
                    <a href="/test/slow-search" class="text-red-600 hover:text-red-900">Test Búsqueda</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4">
        @yield('content')
    </main>

    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4">
            <div class="text-center text-gray-600">
                <p>🚨 Esta es la rama de <strong>PROBLEMAS</strong> - Demuestra queries lentas y N+1 queries</p>
                <p class="text-sm mt-2">Usa Laravel Debugbar para ver las queries ejecutadas</p>
            </div>
        </div>
    </footer>
</body>
</html>
