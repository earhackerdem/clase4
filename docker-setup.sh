#!/bin/bash

# Script de configuración automática del entorno Docker para Laravel

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${YELLOW}║                                                            ║${NC}"
echo -e "${YELLOW}║     🚀 Laravel 12 - Configuración de Entorno Docker       ║${NC}"
echo -e "${YELLOW}║                                                            ║${NC}"
echo -e "${YELLOW}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Verificar que Docker está corriendo
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Docker no está corriendo${NC}"
    exit 1
fi

echo -e "${GREEN}✓${NC} Docker está corriendo"

# Verificar que docker-compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ Error: docker-compose no está instalado${NC}"
    exit 1
fi

echo -e "${GREEN}✓${NC} docker-compose está instalado"

# Crear directorios necesarios
echo ""
echo -e "${YELLOW}📁 Creando directorios necesarios...${NC}"
mkdir -p storage/logs/mysql
echo -e "${GREEN}✓${NC} Directorios creados"

# Construir imágenes
echo ""
echo -e "${YELLOW}🔨 Construyendo imágenes Docker...${NC}"
docker-compose build

# Levantar contenedores
echo ""
echo -e "${YELLOW}🚀 Levantando contenedores...${NC}"
docker-compose up -d

# Esperar a que MySQL esté listo
echo ""
echo -e "${YELLOW}⏳ Esperando a que MySQL esté listo...${NC}"
sleep 10

MAX_TRIES=30
COUNTER=0
until docker-compose exec -T mysql mysqladmin ping -h localhost -u root -ppassword --silent 2>/dev/null; do
    COUNTER=$((COUNTER+1))
    if [ $COUNTER -gt $MAX_TRIES ]; then
        echo -e "${RED}❌ Error: MySQL no respondió a tiempo${NC}"
        exit 1
    fi
    echo -e "${YELLOW}.${NC}"
    sleep 2
done

echo -e "${GREEN}✓${NC} MySQL está listo"

# Ejecutar migraciones
echo ""
echo -e "${YELLOW}🗄️  Ejecutando migraciones...${NC}"
docker-compose exec -T app php artisan migrate --force

echo -e "${GREEN}✓${NC} Migraciones completadas"

# Limpiar caches
echo ""
echo -e "${YELLOW}🧹 Limpiando cachés...${NC}"
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan route:clear
docker-compose exec -T app php artisan view:clear

echo -e "${GREEN}✓${NC} Cachés limpiadas"

# Ajustar permisos de logs de MySQL
echo ""
echo -e "${YELLOW}🔐 Ajustando permisos de logs de MySQL...${NC}"
docker-compose exec -T mysql bash -c "chmod 644 /var/log/mysql/*.log 2>/dev/null || true" 2>/dev/null || true
chmod -R 644 storage/logs/mysql/*.log 2>/dev/null || true

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
