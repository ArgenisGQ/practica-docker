# =============================================================================
# UNINOTAS - DOCKERFILE SQL SERVER 2022
# Contenedor B: Microsoft SQL Server 2022 con scripts de inicio automatizados.
# =============================================================================

FROM mcr.microsoft.com/mssql/server:2022-latest

# Cambiar a usuario root temporalmente para copiar archivos y ajustar permisos
USER root

# Crear directorio de scripts de inicialización
RUN mkdir -p /docker-entrypoint-initdb.d

# Copiar scripts SQL de estructura, usuarios y carga inicial
COPY init/01_schema.sql /docker-entrypoint-initdb.d/01_schema.sql
COPY init/02_users.sql /docker-entrypoint-initdb.d/02_users.sql
COPY init/03_seed.sql /docker-entrypoint-initdb.d/03_seed.sql

# Copiar script de entrypoint
COPY entrypoint.sh /entrypoint.sh

# Otorgar permisos de ejecución al entrypoint y asegurar saltos de línea LF (Unix)
RUN chmod +x /entrypoint.sh && \
    sed -i -e 's/\r$//' /entrypoint.sh

# Volver al usuario por defecto de SQL Server (mssql) por buenas prácticas de seguridad
USER mssql

# Definir el comando de inicio
ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]
