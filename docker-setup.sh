#!/bin/bash

# Script de configuración automática del entorno Docker para Laravel

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${YELLOW}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${YELLOW}║                                                            ║${NC}"
echo -e "${YELLOW}║     🚀 Laravel 12 - Configuración de Entorno Docker       ║${NC}"
echo -e "${YELLOW}║                                                            ║${NC}"
echo -e "${YELLOW}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Detectar sistema operativo para compatibilidad con sed
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS (BSD sed)
    SED_INPLACE=(-i '')
else
    # Linux/WSL (GNU sed)
    SED_INPLACE=(-i)
fi

# Verificar que Docker está corriendo
echo -e "${BLUE}[1/12]${NC} Verificando Docker..."
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Docker no está corriendo${NC}"
    exit 1
fi
echo -e "${GREEN}✓${NC} Docker está corriendo"

# Verificar que docker compose está disponible
echo -e "${BLUE}[2/12]${NC} Verificando Docker Compose..."
if ! docker compose version > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Docker Compose no está disponible${NC}"
    echo -e "${YELLOW}Instala Docker Compose siguiendo: https://docs.docker.com/compose/install/${NC}"
    exit 1
fi
echo -e "${GREEN}✓${NC} Docker Compose está disponible"

# Obtener UID y GID del usuario actual
echo -e "${BLUE}[3/12]${NC} Detectando UID y GID del usuario..."
CURRENT_UID=$(id -u)
CURRENT_GID=$(id -g)
echo -e "${GREEN}✓${NC} UID: ${CURRENT_UID}, GID: ${CURRENT_GID}"

# Configurar archivo .env
echo -e "${BLUE}[4/12]${NC} Configurando archivo .env..."
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        # Actualizar UID y GID en el .env
        sed "${SED_INPLACE[@]}" "s/^UID=.*/UID=${CURRENT_UID}/" .env
        sed "${SED_INPLACE[@]}" "s/^GID=.*/GID=${CURRENT_GID}/" .env
        echo -e "${GREEN}✓${NC} Archivo .env creado desde .env.example"
    else
        echo -e "${RED}❌ Error: No existe .env.example${NC}"
        exit 1
    fi
else
    # Actualizar UID y GID en el .env existente
    sed "${SED_INPLACE[@]}" "s/^UID=.*/UID=${CURRENT_UID}/" .env
    sed "${SED_INPLACE[@]}" "s/^GID=.*/GID=${CURRENT_GID}/" .env
    echo -e "${GREEN}✓${NC} Archivo .env actualizado con UID y GID"
fi

# Crear directorios necesarios
echo -e "${BLUE}[5/12]${NC} Creando directorios necesarios..."
mkdir -p storage/logs/mysql
mkdir -p storage/framework/{cache,sessions,testing,views}
mkdir -p storage/app/public
mkdir -p bootstrap/cache
echo -e "${GREEN}✓${NC} Directorios creados"

# Detener contenedores previos si existen
echo -e "${BLUE}[6/12]${NC} Deteniendo contenedores previos (si existen)..."
docker compose down > /dev/null 2>&1 || true
echo -e "${GREEN}✓${NC} Contenedores previos detenidos"

# Construir imágenes
echo -e "${BLUE}[7/12]${NC} Construyendo imágenes Docker..."
docker compose build --no-cache
echo -e "${GREEN}✓${NC} Imágenes construidas"

# Levantar contenedores
echo -e "${BLUE}[8/12]${NC} Levantando contenedores..."
docker compose up -d
echo -e "${GREEN}✓${NC} Contenedores levantados"

# Esperar a que MySQL esté listo
echo -e "${BLUE}[9/12]${NC} Esperando a que MySQL esté listo..."
MAX_TRIES=30
COUNTER=0
until docker compose exec -T mysql mysqladmin ping -h localhost -u root -ppassword --silent 2>/dev/null; do
    COUNTER=$((COUNTER+1))
    if [ $COUNTER -gt $MAX_TRIES ]; then
        echo -e "${RED}❌ Error: MySQL no respondió a tiempo${NC}"
        echo -e "${YELLOW}Ejecuta 'docker compose logs mysql' para ver los logs${NC}"
        exit 1
    fi
    printf "${YELLOW}.${NC}"
    sleep 2
done
echo ""
echo -e "${GREEN}✓${NC} MySQL está listo"

# Instalar dependencias de Composer
echo -e "${BLUE}[10/12]${NC} Instalando dependencias de Composer..."
docker compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader
echo -e "${GREEN}✓${NC} Dependencias instaladas"

# Generar APP_KEY si no existe
echo -e "${BLUE}[11/12]${NC} Generando APP_KEY..."
if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
    docker compose exec -T app php artisan key:generate --force
    echo -e "${GREEN}✓${NC} APP_KEY generada"
else
    echo -e "${GREEN}✓${NC} APP_KEY ya existe"
fi

# Ejecutar migraciones y seeders
echo -e "${BLUE}[12/12]${NC} Ejecutando migraciones y seeders..."
docker compose exec -T app php artisan migrate --seed --force
echo -e "${GREEN}✓${NC} Migraciones y seeders completados"

# Limpiar y optimizar
echo ""
echo -e "${YELLOW}🧹 Limpiando cachés y optimizando...${NC}"
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan view:clear
docker compose exec -T app php artisan optimize
echo -e "${GREEN}✓${NC} Cachés limpiadas y optimización completada"

# Crear symlink de storage
echo ""
echo -e "${YELLOW}🔗 Creando symlink de storage público...${NC}"
docker compose exec -T app php artisan storage:link
echo -e "${GREEN}✓${NC} Symlink creado"

# Ajustar permisos
echo ""
echo -e "${YELLOW}🔐 Ajustando permisos...${NC}"
# Ajustar permisos de logs de MySQL dentro del contenedor
docker compose exec -T mysql bash -c "chmod 644 /var/log/mysql/*.log 2>/dev/null || true" 2>/dev/null || true

# Ajustar permisos solo de directorios (no archivos .gitignore)
find storage -type d -exec chmod 755 {} \; 2>/dev/null || true
find bootstrap/cache -type d -exec chmod 755 {} \; 2>/dev/null || true

# Ajustar permisos de archivos de log
find storage/logs -type f -name "*.log" -exec chmod 644 {} \; 2>/dev/null || true

# Restaurar permisos correctos de archivos .gitignore (644)
find storage -type f -name ".gitignore" -exec chmod 644 {} \; 2>/dev/null || true
find bootstrap/cache -type f -name ".gitignore" -exec chmod 644 {} \; 2>/dev/null || true

echo -e "${GREEN}✓${NC} Permisos ajustados"

# Mostrar información de acceso
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                            ║${NC}"
echo -e "${GREEN}║  ✅ ¡Entorno configurado exitosamente!                    ║${NC}"
echo -e "${GREEN}║                                                            ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}📋 URLs de acceso:${NC}"
echo ""
echo -e "   🌐 Aplicación Laravel:  ${GREEN}http://localhost:8000${NC}"
echo -e "   🐬 phpMyAdmin:          ${GREEN}http://localhost:8080${NC}"
echo -e "      └─ Usuario: root"
echo -e "      └─ Contraseña: password"
echo -e "   📧 Mailpit (Email):     ${GREEN}http://localhost:8025${NC}"
echo ""
echo -e "${YELLOW}🔧 Comandos útiles:${NC}"
echo ""
echo -e "   make help              Ver todos los comandos disponibles"
echo -e "   make logs              Ver logs de todos los servicios"
echo -e "   make shell             Acceder al contenedor"
echo -e "   make mysql             Acceder a MySQL"
echo -e "   make slow-queries      Ver queries lentas"
echo ""
echo -e "${YELLOW}📊 Monitoreo de MySQL:${NC}"
echo ""
echo -e "   Slow Query Log: storage/logs/mysql/slow-query.log"
echo -e "   Umbral: 1 segundo"
echo ""
echo -e "${GREEN}🎉 ¡Listo para desarrollar!${NC}"
echo ""
