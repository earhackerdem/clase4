# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application running in a fully Dockerized environment with MySQL 8.4, Redis, Nginx, phpMyAdmin, and Mailpit. The project uses Docker Compose v2 (not the legacy `docker-compose` command) for container orchestration.

## Quick Setup

For new developers or fresh installations:

```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

The automated setup script handles:
- Docker/Docker Compose verification
- UID/GID detection and configuration
- Environment file creation
- Container build and startup
- MySQL readiness checks
- Composer dependencies installation
- APP_KEY generation
- Database migrations and seeding
- Cache optimization
- Permission fixes

## Development Environment

### Docker Services Architecture

The application runs across multiple interconnected Docker containers:

- **app**: PHP 8.3-FPM running as non-root user (laravel) with UID/GID matching host user
- **nginx**: Alpine-based web server proxying to PHP-FPM
- **mysql**: MySQL 8.4 LTS with slow query logging and Performance Schema enabled
- **redis**: Redis 7 for cache, sessions, and queues
- **phpmyadmin**: Database administration UI
- **mailpit**: Email testing interface

All services communicate via the `laravel` Docker network. MySQL and Redis have health checks that must pass before the app container starts.

### Container Access

**IMPORTANT**: All Laravel commands must be executed inside the Docker container, not on the host.

The Makefile supports intuitive pass-through syntax for `artisan` and `composer` commands:

```bash
# Enter the app container
make shell                                    # as laravel user
make shell-root                               # as root user

# Run Artisan commands (clean syntax - just like running artisan directly)
make artisan migrate                          # execute migrations
make artisan make:model Post -mfs             # create model with migration, factory, seeder
make artisan route:list                       # list all routes
make artisan queue:work --tries=3             # run queue worker with options
make artisan db:seed --class=UserSeeder       # run specific seeder

# Run Composer (clean syntax - just like running composer directly)
make composer install                         # install dependencies
make composer require laravel/sanctum         # add package
make composer update                          # update dependencies
make composer dump-autoload                   # regenerate autoload files

# Database access
make mysql                                    # connect as laravel user
make mysql-root                               # connect as root
make redis-cli                                # connect to Redis
```

**Without Make** (using `docker compose` v2 directly):

```bash
docker compose exec app php artisan migrate
docker compose exec app composer install
docker compose exec mysql mysql -u laravel -ppassword laravel
```

### Common Development Commands

```bash
# Container management
make build                      # Build Docker images
make up                         # Start containers
make down                       # Stop containers
make restart                    # Restart all services
make logs                       # View all logs (follows)
make logs-app                   # View app logs only
make logs-mysql                 # View MySQL logs only

# Database operations
make migrate                    # Run pending migrations
make migrate-fresh              # Drop all tables and re-migrate
make seed                       # Run database seeders
make migrate-seed               # Migrate and seed in one command

# Testing
make test                       # Run PHPUnit test suite
make pint                       # Format code with Laravel Pint
make pint-test                  # Check code style without modifying

# Cache management
make cache-clear                # Clear all Laravel caches
make optimize                   # Optimize application for production
make optimize-clear             # Clear optimizations

# Queue management
make queue-work                 # Run queue worker (processes once)
make queue-listen               # Listen to queue continuously

# Complete fresh installation
make fresh-install              # build + up + migrate + seed
```

## Testing

The project uses PHPUnit configured in `phpunit.xml`:

- **Unit tests**: `tests/Unit/` - isolated tests
- **Feature tests**: `tests/Feature/` - integration tests
- Test database: In-memory SQLite (not the Docker MySQL)
- Run tests: `make test` or `docker compose exec app php artisan test`

### Running Single Tests

```bash
# Run specific test file
make artisan test tests/Feature/ExampleTest.php

# Run specific test method
make artisan test --filter test_method_name

# Run with coverage
make artisan test --coverage

# Run tests in parallel
make artisan test --parallel
```

## Database & MySQL

### Slow Query Monitoring

The MySQL container is configured to log slow queries (>1 second) and queries not using indexes:

```bash
# View slow query log
make slow-queries

# Generate test slow query
make test-slow-query              # 2 seconds default
make test-slow-query SECONDS=5    # custom duration

# Check MySQL status
make mysql-status

# View active processes
make mysql-processlist
```

Log location: `storage/logs/mysql/slow-query.log`

Configuration: `docker/mysql/my.cnf`
- `long_query_time = 1` (queries >1 second)
- `log_queries_not_using_indexes = 1`
- Performance Schema enabled for advanced analysis

### Performance Schema Queries

Access MySQL as root and run:

```sql
-- Find slowest queries
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

## File Permissions (Linux/WSL)

The Docker container runs as a non-root user with UID/GID matching the host user to avoid permission issues. This is automatically configured by `docker-setup.sh`.

If you encounter permission errors:

```bash
# Automatic fix (recommended)
./docker-setup.sh

# Manual fix
make fix-permissions

# Verify your UID/GID in .env matches your user
id -u    # Should match UID in .env
id -g    # Should match GID in .env
```

## Environment Configuration

Key environment variables in `.env`:

```env
# Database (internal Docker hostnames)
DB_HOST=mysql                   # NOT localhost
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password

# Redis (internal Docker hostname)
REDIS_HOST=redis                # NOT localhost
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (Mailpit for development)
MAIL_HOST=mailpit               # NOT localhost
MAIL_PORT=1025

# Docker port mappings (host access)
APP_PORT=8000                   # Laravel: http://localhost:8000
PHPMYADMIN_PORT=8080           # phpMyAdmin: http://localhost:8080
MAILPIT_UI_PORT=8025           # Mailpit: http://localhost:8025

# User permissions (auto-configured)
UID=1000                        # Set by docker-setup.sh
GID=1000                        # Set by docker-setup.sh
```

**Important**: When connecting from inside Docker containers, always use service names (mysql, redis, mailpit) NOT localhost or 127.0.0.1.

## Code Standards

- **Code formatting**: Use Laravel Pint before committing: `make pint`
- **PSR-12**: Code follows PSR-12 standards via Pint
- **Check before commit**: Run `make pint-test` to verify without modifying files
- **Namespaces**:
  - Models: `App\Models`
  - Controllers: `App\Http\Controllers`
  - Providers: `App\Providers`

## Creating New Components

When creating new Laravel components, use the make commands:

```bash
# Models
make artisan make:model Post -m              # with migration
make artisan make:model Post -mfs            # with migration, factory, seeder

# Controllers
make artisan make:controller PostController
make artisan make:controller PostController --resource
make artisan make:controller Api/PostController --api

# Migrations
make artisan make:migration create_posts_table
make artisan make:migration add_status_to_posts_table

# Seeders and Factories
make artisan make:seeder PostSeeder
make artisan make:factory PostFactory

# Form Requests
make artisan make:request StorePostRequest

# Jobs and Events
make artisan make:job ProcessPost
make artisan make:event PostCreated
make artisan make:listener SendPostNotification

# Middleware
make artisan make:middleware CheckPostOwnership
```

## Project Structure

```
app/
├── Http/Controllers/     # HTTP controllers
├── Models/              # Eloquent models
└── Providers/           # Service providers

database/
├── factories/           # Model factories
├── migrations/          # Database migrations
└── seeders/            # Database seeders

docker/
├── mysql/my.cnf        # MySQL configuration (slow query log)
├── nginx/default.conf  # Nginx server configuration
└── php/                # PHP configuration (php.ini, opcache.ini)

routes/
├── web.php             # Web routes
└── console.php         # Artisan commands

tests/
├── Feature/            # Feature tests
└── Unit/              # Unit tests
```

## Docker Compose v2

This project uses Docker Compose v2 (`docker compose` without hyphen), not the legacy v1 (`docker-compose` with hyphen).

**Correct commands**:
```bash
docker compose up
docker compose down
docker compose exec app bash
```

**Incorrect (legacy v1)**:
```bash
docker-compose up        # DO NOT USE
```

If you need Docker Compose v2, see: https://docs.docker.com/compose/install/

## Troubleshooting

### Containers won't start
```bash
# Check container status
docker compose ps

# View detailed logs
make logs

# Complete reset
docker compose down -v
./docker-setup.sh
```

### Port already in use
Modify ports in `.env`:
```env
APP_PORT=8001
PHPMYADMIN_PORT=8081
```

### Database connection fails
```bash
# Verify MySQL is healthy
docker compose ps

# Check MySQL is ready
make mysql-status

# Restart services
make restart
```

### Permission denied errors
```bash
# Quick fix
make fix-permissions

# Or re-run setup
./docker-setup.sh
```

### Can't read MySQL slow query log
```bash
# Option 1: Fix permissions
make fix-permissions

# Option 2: Read from container
docker compose exec mysql cat /var/log/mysql/slow-query.log
```

## Accessing Services

- **Laravel Application**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080 (root / password)
- **Mailpit UI**: http://localhost:8025
- **MySQL**: localhost:3306 (from host) or mysql:3306 (from containers)
- **Redis**: localhost:6379 (from host) or redis:6379 (from containers)

## Debugging

### Laravel Debug Tools

```bash
# Laravel Pail - Real-time log tailing
make artisan pail

# Tinker - Interactive REPL
make artisan tinker

# View routes
make artisan route:list

# View registered events
make artisan event:list

# View scheduled tasks
make artisan schedule:list
```

### Viewing Logs

```bash
# Application logs
tail -f storage/logs/laravel.log

# Docker container logs
make logs-app                    # Application container
make logs-mysql                  # MySQL container
make logs-nginx                  # Nginx container

# MySQL slow queries
make slow-queries
tail -f storage/logs/mysql/slow-query.log
```

### Database Debugging

```bash
# Check database connection
make artisan db:show

# Show table information
make artisan db:table users

# Monitor database queries (via Pail)
make artisan pail --filter=query
```

## Common Development Workflows

### Adding a New Feature

```bash
# 1. Create model with migration, factory, and seeder
make artisan make:model Post -mfs

# 2. Edit the migration file in database/migrations/
# 3. Edit the factory in database/factories/
# 4. Edit the seeder in database/seeders/

# 5. Run migrations
make migrate

# 6. Create controller
make artisan make:controller PostController --resource

# 7. Add routes to routes/web.php

# 8. Test your changes
make artisan tinker
# >>> App\Models\Post::factory()->count(5)->create()

# 9. Format code
make pint

# 10. Run tests
make test
```

### Resetting Database

```bash
# Option 1: Fresh migration with seeders
make migrate-fresh
make seed

# Option 2: Combined
make artisan migrate:fresh --seed

# Option 3: Complete reset with composer
make down
docker compose down -v          # Remove volumes
make up
make composer install
make migrate-seed
```

### Adding a New Package

```bash
# 1. Add package via composer
make composer require vendor/package

# 2. Publish config if needed
make artisan vendor:publish --provider="Vendor\\Package\\ServiceProvider"

# 3. Clear config cache
make artisan config:clear

# 4. Test the package works
make artisan tinker
```

### Working with Queues

```bash
# 1. Create a job
make artisan make:job ProcessData

# 2. Dispatch the job in your code

# 3. Run the queue worker
make queue-work

# 4. For development, use queue:listen (auto-reloads)
make queue-listen

# 5. View failed jobs
make artisan queue:failed

# 6. Retry failed jobs
make artisan queue:retry all
```

### Database Maintenance

```bash
# Check database status
make artisan db:show

# Inspect table structure
make artisan db:table users

# Create backup (from container)
docker compose exec mysql mysqldump -u root -ppassword laravel > backup.sql

# Restore backup
docker compose exec -T mysql mysql -u root -ppassword laravel < backup.sql

# Monitor slow queries
make slow-queries

# Test query performance
make test-slow-query SECONDS=3
```

## Git Workflow

Current branch: `main`

When creating commits or pull requests, the base branch is `main`.
