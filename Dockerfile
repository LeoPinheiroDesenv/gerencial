# Estágio 1: Construir a aplicação Laravel
FROM composer:2.5 as builder

WORKDIR /app

COPY . .

RUN composer install --ignore-platform-reqs --no-dev --optimize-autoloader --no-interaction

# Estágio 2: Imagem de produção
FROM php:8.1-apache

WORKDIR /var/www/html

# Instalar dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libgd-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    mbstring \
    opcache \
    intl \
    soap \
    gd \
    && a2enmod rewrite

# Configurar o PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copiar arquivos da aplicação
COPY --from=builder /app .
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Configurações personalizadas do PHP
COPY .docker/php.ini /usr/local/etc/php/conf.d/php-custom.ini

# Expor a porta 80
EXPOSE 80
