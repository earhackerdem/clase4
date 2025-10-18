#!/bin/bash
# Script para ajustar permisos de logs de MySQL

# Esperar un momento para que MySQL cree los archivos
sleep 2

# Hacer que los logs sean legibles por el grupo
chmod -R 644 /var/log/mysql/*.log 2>/dev/null || true

echo "Permisos de logs ajustados"
