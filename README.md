# 🚨 Blog de Optimización - Rama de Problemas

Este proyecto demuestra **problemas comunes de rendimiento** en Laravel 12, específicamente diseñado para mostrar el impacto de queries lentas, N+1 queries, falta de índices y ausencia de cache.

## 📋 Estructura del Proyecto

### 🌿 Ramas
- **`problems-branch`** (actual): Contiene todos los problemas de rendimiento
- **`solutions-branch`**: Contendrá las optimizaciones (próximamente)

### 🗄️ Base de Datos
- **MySQL 8.4** con configuración de monitoreo de queries lentas
- **Redis** para cache (no implementado en esta rama)
- **Datos masivos** para generar problemas de rendimiento reales

## 🚨 Problemas de Rendimiento Implementados

### 1. **N+1 Queries Masivas**
```php
// ❌ PROBLEMA: N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    $post->user;        // Query adicional por cada post
    $post->category;    // Query adicional por cada post
    $post->tags;        // Query adicional por cada post
}
```

### 2. **Queries Lentas Sin Índices**
```php
// ❌ PROBLEMA: Búsqueda sin índices full-text
$posts = Post::where('title', 'like', "%{$term}%")
    ->orWhere('content', 'like', "%{$term}%")
    ->get();
```

### 3. **Falta de Cache**
```php
// ❌ PROBLEMA: Sin cache en consultas frecuentes
$popularPosts = Post::orderBy('likes_count', 'desc')
    ->take(10)
    ->get();
```

### 4. **Carga Innecesaria de Datos**
```php
// ❌ PROBLEMA: Carga todos los campos sin select específico
$posts = Post::all();
```

## 📊 Datos de Prueba

| Tabla | Registros | Propósito |
|-------|-----------|-----------|
| Users | 1,000 | Generar N+1 queries en relaciones |
| Categories | 50 | Demostrar queries lentas sin índices |
| Tags | 200 | Mostrar problemas en relaciones many-to-many |
| Posts | 10,000 | Base para problemas de rendimiento |
| Comments | 50,000+ | N+1 queries masivas |
| Likes | 100,000 | Queries lentas en relaciones polimórficas |
| Views | 200,000 | Estadísticas sin optimización |

## 🛠️ Configuración

### Docker
```bash
# Iniciar servicios
docker-compose up -d

# Ver logs de MySQL (queries lentas)
docker-compose logs -f mysql
```

### Laravel
```bash
# Instalar dependencias
composer install

# Configurar base de datos
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Poblar base de datos con datos masivos
php artisan db:seed
```

## 🧪 Pruebas de Rendimiento

### Rutas de Prueba
- **`/test/n-plus-one`**: Demuestra N+1 queries
- **`/test/slow-search`**: Búsquedas lentas sin índices
- **`/test/stats-no-cache`**: Estadísticas sin cache
- **`/test/popular-posts`**: Posts populares sin optimización

### Páginas con Problemas
- **`/dashboard`**: Dashboard con múltiples queries lentas
- **`/posts`**: Lista de posts con N+1 queries masivas
- **`/search`**: Búsqueda global sin optimización

## 📈 Monitoreo de Rendimiento

### MySQL Slow Query Log
```bash
# Ver queries lentas
docker exec -it laravel_mysql tail -f /var/log/mysql/slow-query.log
```

### Laravel Debugbar
- Instalar: `composer require barryvdh/laravel-debugbar --dev`
- Ver número de queries y tiempo de ejecución

### Métricas a Observar
- **Número de queries**: Antes vs Después
- **Tiempo de ejecución**: Milisegundos
- **Uso de memoria**: MB
- **Queries lentas**: En slow query log

## 🎯 Escenarios de Demostración

### Escenario 1: Lista de Posts
- **Problema**: N+1 queries masivas
- **Impacto**: 1 + (10,000 × 5) = 50,001 queries
- **Tiempo**: ~5-10 segundos

### Escenario 2: Búsqueda Global
- **Problema**: LIKE queries sin índices
- **Impacto**: Full table scan en 10,000+ registros
- **Tiempo**: ~2-5 segundos

### Escenario 3: Dashboard
- **Problema**: Múltiples queries separadas
- **Impacto**: 20+ queries para estadísticas
- **Tiempo**: ~1-3 segundos

## 🔍 Herramientas de Análisis

### 1. Laravel Debugbar
```bash
composer require barryvdh/laravel-debugbar --dev
```

### 2. Laravel Telescope
```bash
composer require laravel/telescope --dev
php artisan telescope:install
```

### 3. MySQL Performance Schema
```sql
-- Ver queries más lentas
SELECT * FROM performance_schema.events_statements_summary_by_digest 
ORDER BY AVG_TIMER_WAIT DESC LIMIT 10;
```

## 📚 Conceptos Demostrados

### 1. **N+1 Problem**
- El problema más común en Laravel
- Cómo identificar y medir
- Impacto en rendimiento

### 2. **Índices de Base de Datos**
- Primary, Foreign, Full-text
- Índices compuestos
- Cuándo usar cada tipo

### 3. **Eager Loading**
- `with()`, `load()`, `loadMissing()`
- Diferencia con lazy loading
- Mejores prácticas

### 4. **Cache**
- Redis vs Memcached
- TTL y invalidación
- Estrategias de cache

### 5. **Query Optimization**
- `select()` específico
- `whereHas()` vs `has()`
- Paginación y límites

## 🚀 Próximos Pasos

1. **Crear rama de soluciones**
2. **Implementar optimizaciones**
3. **Comparar métricas de rendimiento**
4. **Documentar mejoras**

## 📖 Recursos Adicionales

- [Laravel Eloquent Performance](https://laravel.com/docs/eloquent)
- [MySQL Indexing Best Practices](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)
- [Redis Caching Strategies](https://redis.io/docs/manual/patterns/)

---

**⚠️ Nota**: Esta rama está diseñada para demostrar problemas de rendimiento. No usar en producción.