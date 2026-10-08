# =============================================================================
# UNINOTAS - DOCKERFILE CONTENEDOR WEB (PHP 8.0 + APACHE + PDO SQLSRV)
# =============================================================================

FROM php:8.0-apache

# Evitar prompts interactivos durante apt-get
ENV DEBIAN_FRONTEND=noninteractive

# Corregir espejos de debian-security si responden 404 (Debian Bullseye EOL/Archive)
RUN sed -i 's|http://deb.debian.org/debian-security|http://archive.debian.org/debian-security|g' /etc/apt/sources.list || true

# 1. Instalar paquetes de sistema esenciales y librerías de desarrollo
RUN apt-get update --allow-releaseinfo-change && apt-get install -y --no-install-recommends \
    gnupg2 \
    curl \
    apt-transport-https \
    unixodbc-dev \
    libzip-dev \
    libonig-dev \
    unzip \
    zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Agregar el repositorio de Microsoft e instalar MS ODBC Driver 18 para Linux
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /etc/apt/trusted.gpg.d/microsoft.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 mssql-tools18 \
    && rm -rf /var/lib/apt/lists/*

# 3. Compilar e instalar los controladores PHP oficiales de Microsoft (sqlsrv y pdo_sqlsrv v5.11.1)
RUN pecl install sqlsrv-5.11.1 pdo_sqlsrv-5.11.1 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# 4. Instalar extensiones PHP requeridas (mbstring, zip)
RUN docker-php-ext-install mbstring zip

# 5. Habilitar el módulo mod_rewrite de Apache para URLs amigables
RUN a2enmod rewrite

# 6. Reconfigurar DocumentRoot a /var/www/html/public para aislar assets estáticos y Front Controller
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 7. Configurar Apache Directory para permitir anulaciones con .htaccess (AllowOverride All)
RUN echo '<Directory /var/www/html/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/uninotas.conf \
    && a2enconf uninotas

# 8. Copiar el código fuente del proyecto al directorio web
COPY . /var/www/html/

# 9. Asignar propiedad de los archivos al usuario del servidor web www-data
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
