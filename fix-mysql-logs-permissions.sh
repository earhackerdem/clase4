#!/bin/bash

# Script para ajustar permisos de logs de MySQL
# Ejecutar después de que los contenedores estén corriendo

echo "Ajustando permisos de logs de MySQL..."

# Cambiar permisos dentro del contenedor
docker-compose exec -T mysql bash -c "chmod 644 /var/log/mysql/*.log 2>/dev/null || true"

# Cambiar permisos en el host (requiere que el directorio sea accesible)
chmod -R 644 storage/logs/mysql/*.log 2>/dev/null || true

echo "✓ Permisos ajustados. Ahora puedes leer los logs sin sudo:"
echo "  cat storage/logs/mysql/slow-query.log"
echo "  make slow-queries"
