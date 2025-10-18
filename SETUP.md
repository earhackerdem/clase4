# 🚀 Guía de Configuración - Blog de Optimización

## 📋 Requisitos Previos

- Docker y Docker Compose
- Git
- Composer (opcional, se ejecuta en Docker)

## 🛠️ Configuración Paso a Paso

### 1. Clonar y Configurar
```bash
# Clonar el repositorio
git clone <repository-url>
cd clase4

# Verificar que estás en la rama de problemas
git branch
# Debería mostrar: * problems-branch
```

### 2. Configurar Docker
```bash
# Iniciar servicios
docker-compose up -d

# Verificar que todos los servicios estén corriendo
docker-compose ps
```

### 3. Configurar Laravel
```bash
# Instalar dependencias
docker-compose exec app composer install

# Configurar archivo de entorno
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

# Verificar conexión a base de datos
docker-compose exec app php artisan migrate:status
```

### 4. Poblar Base de Datos
```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Poblar con datos masivos (esto tomará varios minutos)
docker-compose exec app php artisan db:seed
```

### 5. Verificar Configuración
```bash
# Verificar que los datos se crearon correctamente
docker-compose exec app php artisan tinker
```

En Tinker:
```php
// Verificar conteos
App\Models\User::count();        // Debería ser 1,000
App\Models\Post::count();        // Debería ser 10,000
App\Models\Comment::count();     // Debería ser 50,000+
App\Models\Like::count();        // Debería ser 100,000
App\Models\View::count();        // Debería ser 200,000

// Salir de Tinker
exit
```

## 🧪 Pruebas de Rendimiento

### 1. Instalar Laravel Debugbar
```bash
docker-compose exec app composer require barryvdh/laravel-debugbar --dev
```

### 2. Probar Rutas de Rendimiento
```bash
# Probar N+1 queries
curl http://localhost:8000/test/n-plus-one

# Probar búsqueda lenta
curl http://localhost:8000/test/slow-search?q=laravel

# Probar estadísticas sin cache
curl http://localhost:8000/test/stats-no-cache
```

### 3. Verificar Slow Query Log
```bash
# Ver queries lentas en MySQL
docker-compose exec mysql tail -f /var/log/mysql/slow-query.log
```

## 🌐 Acceso a la Aplicación

### URLs Principales
- **Aplicación**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
- **Mailpit**: http://localhost:8025

### Credenciales
- **MySQL**: usuario `laravel`, contraseña `password`
- **phpMyAdmin**: usuario `root`, contraseña `password`

## 📊 Monitoreo de Rendimiento

### 1. Laravel Debugbar
- Instalar y acceder a cualquier página
- Ver número de queries y tiempo de ejecución
- Identificar N+1 queries

### 2. MySQL Slow Query Log
```bash
# Ver queries lentas
docker-compose exec mysql tail -f /var/log/mysql/slow-query.log

# Ver queries que no usan índices
docker-compose exec mysql grep "No index used" /var/log/mysql/slow-query.log
```

### 3. Métricas de Rendimiento
```bash
# Probar rendimiento de diferentes endpoints
time curl http://localhost:8000/posts
time curl http://localhost:8000/dashboard
time curl http://localhost:8000/search?q=laravel
```

## 🔍 Análisis de Problemas

### 1. Identificar N+1 Queries
```php
// En Tinker
$posts = App\Models\Post::take(10)->get();
foreach ($posts as $post) {
    $post->user; // Esto genera N+1 queries
}
```

### 2. Verificar Queries Lentas
```sql
-- En phpMyAdmin o MySQL CLI
SHOW PROCESSLIST;
SHOW FULL PROCESSLIST;
```

### 3. Analizar Uso de Índices
```sql
-- Verificar si se usan índices
EXPLAIN SELECT * FROM posts WHERE title LIKE '%laravel%';
```

## 🚨 Problemas Comunes

### 1. Error de Conexión a Base de Datos
```bash
# Verificar que MySQL esté corriendo
docker-compose exec mysql mysql -u root -p -e "SELECT 1"
```

### 2. Error de Permisos
```bash
# Arreglar permisos
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### 3. Error de Memoria
```bash
# Aumentar memoria para seeders
docker-compose exec app php -d memory_limit=512M artisan db:seed
```

## 📈 Métricas Esperadas

### Antes de Optimizar (Rama Actual)
- **Posts Index**: ~50,000 queries, 5-10 segundos
- **Dashboard**: ~20 queries, 1-3 segundos
- **Búsqueda**: ~10,000 queries, 2-5 segundos
- **N+1 Test**: ~30,000 queries, 3-8 segundos

### Después de Optimizar (Rama Solutions)
- **Posts Index**: ~5 queries, 100-300ms
- **Dashboard**: ~3 queries, 50-100ms
- **Búsqueda**: ~2 queries, 50-150ms
- **N+1 Test**: ~3 queries, 100-200ms

## 🎯 Próximos Pasos

1. **Analizar problemas actuales**
2. **Crear rama de soluciones**
3. **Implementar optimizaciones**
4. **Comparar métricas**
5. **Documentar mejoras**

---

**💡 Tip**: Usa Laravel Debugbar para ver las queries en tiempo real y identificar problemas de rendimiento.
