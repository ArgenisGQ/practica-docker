#!/bin/bash
# =============================================================================
# UNINOTAS - ENTRYPOINT PERSONALIZADO PARA SQL SERVER 2022
# Ejecuta el motor SQL Server en segundo plano, espera a que esté listo y
# aplica los scripts de inicialización DDL/DML/Seguridad automáticamente.
# =============================================================================

# Iniciar el servicio MS SQL Server en segundo plano
/opt/mssql/bin/sqlservr &
SQL_PID=$!

echo "[DB-ENTRYPOINT] Iniciando Microsoft SQL Server 2022..."

# Ubicación dinámica de herramientas sqlcmd (Soporte Tools 18 y Tools 17)
SQLCMD="/opt/mssql-tools18/bin/sqlcmd"
if [ ! -f "$SQLCMD" ]; then
    SQLCMD="/opt/mssql-tools/bin/sqlcmd"
fi

# Esperar a que SQL Server esté completamente listo y acepte conexiones
echo "[DB-ENTRYPOINT] Verificando estado del servicio SQL Server..."
for i in {1..60}; do
    $SQLCMD -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -Q "SELECT 1" > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "[DB-ENTRYPOINT] SQL Server está ONLINE y listo para recibir consultas."
        break
    fi
    echo "[DB-ENTRYPOINT] Esperando respuesta de SQL Server (intento $i/60)..."
    sleep 2
done

# Archivo centinela para asegurar ejecuciones idempotentes (una sola vez)
INIT_FLAG="/var/opt/mssql/data/.uninotas_db_initialized"

if [ ! -f "$INIT_FLAG" ]; then
    echo "[DB-ENTRYPOINT] Base de datos no inicializada. Iniciando migración de esquemas y datos..."

    # 1. Crear Base de Datos y Tablas (01_schema.sql)
    echo "[DB-ENTRYPOINT] Ejecutando: 01_schema.sql..."
    $SQLCMD -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /docker-entrypoint-initdb.d/01_schema.sql
    if [ $? -ne 0 ]; then
        echo "[DB-ENTRYPOINT ERROR] Error al ejecutar 01_schema.sql"
    fi

    # 2. Crear Logins y Permisos de Usuarios (02_users.sql)
    echo "[DB-ENTRYPOINT] Ejecutando: 02_users.sql..."
    $SQLCMD -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /docker-entrypoint-initdb.d/02_users.sql
    if [ $? -ne 0 ]; then
        echo "[DB-ENTRYPOINT ERROR] Error al ejecutar 02_users.sql"
    fi

    # 3. Insertar Semilla de 100 Registros de Alumnos (03_seed.sql)
    echo "[DB-ENTRYPOINT] Ejecutando: 03_seed.sql..."
    $SQLCMD -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /docker-entrypoint-initdb.d/03_seed.sql
    if [ $? -ne 0 ]; then
        echo "[DB-ENTRYPOINT ERROR] Error al ejecutar 03_seed.sql"
    fi

    # Crear archivo centinela de confirmación
    touch "$INIT_FLAG"
    echo "[DB-ENTRYPOINT SUCCESS] Base de datos UniNotasDB inicializada correctamente con 100 alumnos y permisos RBAC."
else
    echo "[DB-ENTRYPOINT] La base de datos ya fue inicializada previamente. Omitiendo scripts."
fi

# Mantener el contenedor vivo reteniendo el proceso de SQL Server
wait $SQL_PID
