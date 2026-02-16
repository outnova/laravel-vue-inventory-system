# Usamos la imagen oficial de PHP 8.2 o 8.3 con FPM (FastCGI Process Manager)
FROM php:8.2-fpm

# Instalamos dependencias del sistema operativo (librerías para manejar imágenes, strings, etc.)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Limpiamos cache para que la imagen pese menos
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalamos extensiones de PHP necesarias para que Laravel y MySQL hablen el mismo idioma
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Conseguimos Composer (el gestor de paquetes de PHP) desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definimos dónde vamos a trabajar dentro del contenedor
WORKDIR /var/www/backend