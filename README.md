# Laravel 12 - Entorno de Desarrollo con Docker

Proyecto Laravel 12 configurado con Docker, MySQL 8.4, Redis, Nginx, phpMyAdmin y Mailpit.

## Stack Tecnológico

- **Laravel**: 12.x
- **PHP**: 8.3-FPM
- **MySQL**: 8.4 LTS
- **Redis**: 7 Alpine
- **Nginx**: Latest Alpine
- **phpMyAdmin**: Latest
- **Mailpit**: Latest (captura de emails)

## Características

### MySQL con Logging de Queries Lentas

El entorno está configurado para registrar queries lentas automáticamente:

- **Umbral**: 1 segundo (`long_query_time = 1`)
- **Log de queries sin índices**: Activado
- **Performance Schema**: Habilitado para análisis avanzado
- **Ubicación del log**: `storage/logs/mysql/slow-query.log`

### Servicios Adicionales

- **phpMyAdmin**: Administración visual de MySQL en `http://localhost:8080`
- **Mailpit**: Captura de emails de desarrollo en `http://localhost:8025`
- **Redis**: Para cache, sesiones y colas

## Requisitos Previos

- Docker
- Docker Compose
- Make (opcional, pero recomendado)

## Instalación

### 1. Clonar el repositorio

```bash
git clone <repository-url> clase4
cd clase4
```

### 2. Configurar variables de entorno

El archivo `.env` ya está configurado, pero verifica y ajusta según necesites:

```bash
# Ajustar UID/GID si es necesario (Linux/WSL)
UID=1000
GID=1000

# Puertos (ajustar si están ocupados)
APP_PORT=8000
PHPMYADMIN_PORT=8080
MAILPIT_UI_PORT=8025
```

### 3. Levantar el entorno

Con Make:
```bash
make build    # Construir imágenes
make up       # Levantar contenedores
make migrate  # Ejecutar migraciones
```

Sin Make:
```bash
docker-compose build
docker-compose up -d
docker-compose exec app php artisan migrate
```

### 4. Acceder a la aplicación

- **Aplicación Laravel**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
  - Usuario: `root`
  - Contraseña: `password`
- **Mailpit (UI)**: http://localhost:8025

## Comandos Make Disponibles

### Gestión de Contenedores

```bash
make help          # Mostrar todos los comandos disponibles
make build         # Construir los contenedores
make up            # Levantar los contenedores
make down          # Detener los contenedores
make restart       # Reiniciar los contenedores
make logs          # Ver logs de todos los servicios
make logs-app      # Ver logs de la aplicación
make logs-mysql    # Ver logs de MySQL
make logs-nginx    # Ver logs de Nginx
```

### Acceso a Contenedores

```bash
make shell         # Acceder al shell del contenedor (usuario laravel)
make shell-root    # Acceder al shell como root
make mysql         # Acceder al cliente MySQL
make mysql-root    # Acceder a MySQL como root
make redis-cli     # Acceder al cliente Redis
```

### Laravel & Artisan

```bash
make composer ARGS="install"        # Ejecutar composer
make artisan ARGS="make:model User" # Ejecutar artisan
make migrate                        # Ejecutar migraciones
make migrate-fresh                  # Recrear BD y migrar
make seed                           # Ejecutar seeders
make migrate-seed                   # Migrar y poblar
make test                           # Ejecutar tests
make pint                           # Formatear código (Laravel Pint)
make pint-test                      # Verificar código sin modificar
```

### Cache y Optimización

```bash
make cache-clear     # Limpiar todas las cachés
make optimize        # Optimizar la aplicación
make optimize-clear  # Limpiar optimizaciones
```

### Colas

```bash
make queue-work      # Ejecutar queue worker
make queue-listen    # Escuchar cola en tiempo real
```

### MySQL - Monitoreo de Queries Lentas

```bash
make test-slow-query           # Generar query lenta de prueba (2 segundos por defecto)
make test-slow-query SECONDS=5 # Generar query lenta personalizada (5 segundos)
make slow-queries              # Ver últimas 50 líneas del log de queries lentas
make mysql-status              # Ver estado y configuración de slow queries
make mysql-processlist         # Ver procesos activos de MySQL
```

### Instalación Completa

```bash
make fresh-install   # Build + Up + Migrate + Seed
```

## Monitoreo de Queries Lentas

### Ver Queries Lentas

```bash
# Desde el host
make slow-queries

# O directamente
tail -f storage/logs/mysql/slow-query.log
```

### Analizar Queries con Performance Schema

```bash
# Acceder a MySQL
make mysql-root

# Ver queries más lentas
SELECT
    DIGEST_TEXT as query,
    COUNT_STAR as exec_count,
    AVG_TIMER_WAIT/1000000000000 as avg_time_sec,
    MAX_TIMER_WAIT/1000000000000 as max_time_sec
FROM performance_schema.events_statements_summary_by_digest
WHERE DIGEST_TEXT IS NOT NULL
ORDER BY AVG_TIMER_WAIT DESC
LIMIT 10;
```

### Verificar Configuración de Slow Query Log

```bash
make mysql-status

# O manualmente
docker-compose exec mysql mysql -u root -ppassword -e "
    SHOW VARIABLES LIKE '%slow%';
    SHOW STATUS LIKE '%Slow_queries%';
"
```

## Estructura del Proyecto

```
clase4/
├── docker/
│   ├── mysql/
│   │   └── my.cnf              # Configuración MySQL con slow query log
│   ├── nginx/
│   │   └── default.conf        # Configuración Nginx
│   └── php/
│       ├── php.ini             # Configuración PHP
│       └── opcache.ini         # Configuración OPcache
├── storage/
│   └── logs/
│       └── mysql/              # Logs de MySQL (slow-query.log)
├── docker-compose.yml          # Definición de servicios Docker
├── Dockerfile                  # Imagen PHP personalizada
├── Makefile                    # Comandos útiles
└── README.md                   # Esta documentación
```

## Configuración de MySQL

### Slow Query Log

El slow query log está configurado en `docker/mysql/my.cnf`:

```ini
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 1                    # Queries que toman > 1 segundo
log_queries_not_using_indexes = 1      # Log queries sin índices
```

### Performance Schema

El Performance Schema está habilitado para análisis avanzado:

```ini
performance_schema = ON
performance-schema-instrument = 'statement/%=ON'
performance-schema-consumer-events-statements-history-long = ON
```

## Solución de Problemas

### Permisos en Linux/WSL

Si tienes problemas de permisos, ajusta UID/GID en `.env`:

```bash
# Obtener tu UID/GID
id -u  # UID
id -g  # GID

# Actualizar .env
UID=1000
GID=1000

# Reconstruir
make down
make build
make up
```

### Permisos de Logs de MySQL

Si no puedes leer los logs de MySQL sin `sudo`:

```bash
# Opción 1: Usar el comando make
make fix-permissions

# Opción 2: Usar el script
./fix-mysql-logs-permissions.sh

# Opción 3: Leer desde el contenedor (siempre funciona)
docker-compose exec mysql cat /var/log/mysql/slow-query.log
```

**Nota**: El script `docker-setup.sh` ajusta automáticamente los permisos durante la instalación inicial.

### Puerto Ocupado

Si un puerto está ocupado, modifica en `.env`:

```bash
APP_PORT=8001           # En lugar de 8000
PHPMYADMIN_PORT=8081    # En lugar de 8080
```

### Ver Logs de Errores

```bash
# Logs de la aplicación
make logs-app

# Logs de MySQL
make logs-mysql

# Logs de Nginx
make logs-nginx

# Logs de Laravel
tail -f storage/logs/laravel.log
```

### Base de Datos No Conecta

```bash
# Verificar que MySQL esté saludable
docker-compose ps

# Reintentar conexión
make restart
make migrate
```

### Limpiar Todo y Empezar de Nuevo

```bash
# Detener y eliminar todo
make down
docker-compose down -v  # Incluye volúmenes

# Reinstalar
make fresh-install
```

## Testing de Queries Lentas

Para probar el logging de queries lentas, usa el comando Make:

```bash
# Generar query lenta de 2 segundos (por defecto)
make test-slow-query

# Generar query lenta personalizada
make test-slow-query SECONDS=3
make test-slow-query SECONDS=10

# Ver el resultado en el log
make slow-queries
```

También puedes hacerlo manualmente:

```bash
# Vía tinker
make shell
php artisan tinker
DB::statement('SELECT SLEEP(2)');
exit

# O directamente desde MySQL
make mysql
SELECT SLEEP(3);
exit
```

## Variables de Entorno Importantes

```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

# Puertos Docker
APP_PORT=8000
PHPMYADMIN_PORT=8080
MAILPIT_UI_PORT=8025
```

## Recursos Adicionales

- [Documentación Laravel 12](https://laravel.com/docs/12.x)
- [MySQL 8.4 Slow Query Log](https://dev.mysql.com/doc/refman/8.4/en/slow-query-log.html)
- [MySQL Performance Schema](https://dev.mysql.com/doc/refman/8.4/en/performance-schema.html)
- [Docker Compose](https://docs.docker.com/compose/)

## Licencia

Este proyecto está bajo la licencia MIT, al igual que Laravel.
