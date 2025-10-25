.PHONY: help build up down restart logs shell composer artisan migrate migrate-fresh seed test pint

help: ## Muestra esta ayuda
	@echo "Comandos disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Construir los contenedores Docker
	docker compose build

up: ## Levantar los contenedores
	docker compose up -d

down: ## Detener los contenedores
	docker compose down

restart: ## Reiniciar los contenedores
	docker compose restart

logs: ## Ver logs de todos los servicios
	docker compose logs -f

logs-app: ## Ver logs de la aplicación
	docker compose logs -f app

logs-mysql: ## Ver logs de MySQL
	docker compose logs -f mysql

logs-nginx: ## Ver logs de Nginx
	docker compose logs -f nginx

shell: ## Acceder al shell del contenedor de la aplicación
	docker compose exec app bash

shell-root: ## Acceder al shell del contenedor como root
	docker compose exec -u root app bash

mysql: ## Acceder al cliente MySQL
	docker compose exec mysql mysql -u laravel -ppassword laravel

mysql-root: ## Acceder a MySQL como root
	docker compose exec mysql mysql -u root -ppassword

redis-cli: ## Acceder al cliente Redis
	docker compose exec redis redis-cli

composer: ## Ejecutar composer (ej: make composer install)
	docker compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

artisan: ## Ejecutar artisan (ej: make artisan route:list)
	docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

# Catch-all target para argumentos de artisan y composer
%:
	@:

migrate: ## Ejecutar migraciones
	docker compose exec app php artisan migrate

migrate-fresh: ## Recrear base de datos y ejecutar migraciones
	docker compose exec app php artisan migrate:fresh

seed: ## Ejecutar seeders
	docker compose exec app php artisan db:seed

migrate-seed: ## Migrar y poblar
	docker compose exec app php artisan migrate --seed

test: ## Ejecutar tests
	docker compose exec app php artisan test

pint: ## Ejecutar Laravel Pint (code styling)
	docker compose exec app ./vendor/bin/pint

pint-test: ## Verificar código con Pint sin modificar
	docker compose exec app ./vendor/bin/pint --test

cache-clear: ## Limpiar cache
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

optimize: ## Optimizar la aplicación
	docker compose exec app php artisan optimize

optimize-clear: ## Limpiar optimizaciones
	docker compose exec app php artisan optimize:clear

queue-work: ## Ejecutar queue worker
	docker compose exec app php artisan queue:work

queue-listen: ## Escuchar cola
	docker compose exec app php artisan queue:listen

fresh-install: build up migrate-seed ## Instalación completa desde cero

slow-queries: ## Ver queries lentas de MySQL
	@echo "=== Últimas 50 líneas del log de queries lentas ==="
	@tail -n 50 storage/logs/mysql/slow-query.log 2>/dev/null || echo "No hay queries lentas registradas aún"

test-slow-query: ## Generar una query lenta de prueba (ajusta SECONDS=N para cambiar duración)
	@echo "Generando query lenta de $(or $(SECONDS),2) segundos..."
	@docker compose exec -T app php artisan tinker --execute="DB::statement('SELECT SLEEP($(or $(SECONDS),2))'); echo 'Query lenta ejecutada: $(or $(SECONDS),2) segundos';"
	@echo ""
	@echo "✓ Query ejecutada. Ver resultado con: make slow-queries"

mysql-status: ## Ver estado de MySQL
	docker compose exec mysql mysql -u root -ppassword -e "SHOW STATUS LIKE '%Slow_queries%'; SHOW VARIABLES LIKE '%slow%';"

mysql-processlist: ## Ver procesos activos de MySQL
	docker compose exec mysql mysql -u root -ppassword -e "SHOW FULL PROCESSLIST;"

fix-permissions: ## Ajustar permisos de storage y logs
	@echo "Ajustando permisos..."
	@docker compose exec -T mysql bash -c "chmod 644 /var/log/mysql/*.log 2>/dev/null || true" 2>/dev/null || true
	@find storage -type d -exec chmod 755 {} \; 2>/dev/null || true
	@find bootstrap/cache -type d -exec chmod 755 {} \; 2>/dev/null || true
	@find storage/logs -type f -name "*.log" -exec chmod 644 {} \; 2>/dev/null || true
	@find storage -type f -name ".gitignore" -exec chmod 644 {} \; 2>/dev/null || true
	@find bootstrap/cache -type f -name ".gitignore" -exec chmod 644 {} \; 2>/dev/null || true
	@echo "✓ Permisos ajustados"
