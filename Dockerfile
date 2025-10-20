FROM php:8.3-fpm

# Argumentos de build
ARG UID=1000
ARG GID=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libpq-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Configurar opcodes cache
RUN docker-php-ext-install opcache

# Copiar configuración personalizada de PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario con el mismo UID/GID del host
# Usar -o para permitir IDs no únicos (compatibilidad con macOS donde GID 20 ya existe)
RUN (groupadd -o -g ${GID} laravel 2>/dev/null || echo "Using existing or non-unique GID") && \
    (useradd -o -u ${UID} -g laravel -m -s /bin/bash laravel 2>/dev/null || echo "Using existing or non-unique UID") && \
    echo "User laravel created/verified successfully"

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Cambiar permisos
RUN chown -R laravel:laravel /var/www/html

# Cambiar a usuario no-root
USER laravel

# Exponer puerto
EXPOSE 9000

CMD ["php-fpm"]
