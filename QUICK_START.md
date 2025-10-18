# Guía de Inicio Rápido - Laravel 12 con Docker

## Inicio Rápido (3 comandos)

```bash
./docker-setup.sh    # Configura todo automáticamente
# O usa Make:
make fresh-install   # Build + Up + Migrate
```

## Acceso Inmediato

| Servicio | URL | Credenciales |
|----------|-----|--------------|
| Laravel App | http://localhost:8000 | - |
| phpMyAdmin | http://localhost:8080 | root / password |
| Mailpit | http://localhost:8025 | - |

## Comandos Esenciales

```bash
# Gestión de Contenedores
make up              # Levantar
make down            # Detener
make logs            # Ver logs
make shell           # Acceder al contenedor

# Laravel
make migrate         # Ejecutar migraciones
make artisan ARGS="make:model Post"
make composer ARGS="require package/name"

# MySQL - Queries Lentas
make test-slow-query         # Generar query lenta de prueba
make test-slow-query SECONDS=5  # Personalizar duración
make slow-queries            # Ver log de queries lentas
make mysql-status            # Ver configuración MySQL
make mysql                   # Acceder a MySQL CLI
```

## Verificar Slow Query Log

```bash
# Generar query lenta de prueba y ver resultado
make test-slow-query
make slow-queries
```

## Estructura de Archivos Docker

```
docker/
├── mysql/my.cnf         → Configuración MySQL (slow query log)
├── nginx/default.conf   → Configuración Nginx
└── php/
    ├── php.ini          → Configuración PHP
    └── opcache.ini      → Configuración OPcache

storage/logs/mysql/      → Logs de MySQL (slow-query.log)
```

## Solución Rápida de Problemas

```bash
# Permisos de logs
make fix-permissions     # Si no puedes leer logs sin sudo

# Reconstruir todo
make down
docker-compose down -v
make fresh-install

# Ver errores
make logs-app
make logs-mysql
tail -f storage/logs/laravel.log
```

## Configuración de Slow Queries

Ubicación: `docker/mysql/my.cnf`

```ini
long_query_time = 1                    # Umbral: 1 segundo
log_queries_not_using_indexes = 1      # Log queries sin índices
slow_query_log_file = /var/log/mysql/slow-query.log
```

## Todos los Comandos Make

Ejecuta `make help` para ver la lista completa de comandos disponibles.
