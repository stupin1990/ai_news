FROM php:8.4-fpm

LABEL maintainer="AI News Project"

ARG WWWGROUP=1000
ARG WWWUSER=1000
ARG NODE_VERSION=22
ARG INSTALL_XDEBUG=true

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libreadline-dev \
    supervisor \
    nano \
    procps \
    ca-certificates \
    gnupg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure GD with image format support
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp

# Install PHP extensions needed for Laravel + PostgreSQL
RUN docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    intl \
    bcmath \
    opcache \
    pcntl \
    sockets \
    exif

# Install Redis via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Optionally install Xdebug
RUN if [ "$INSTALL_XDEBUG" = "true" ] ; then \
        pecl install xdebug && docker-php-ext-enable xdebug ; \
    fi

# Xdebug configuration
RUN echo "xdebug.mode=\${XDEBUG_MODE}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=false" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=VSCODE" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# OPcache configuration
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "realpath_cache_size=4096K" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "realpath_cache_ttl=600" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Custom PHP configuration
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /usr/local/etc/php/php.ini \
    && sed -i 's/post_max_size = 8M/post_max_size = 100M/' /usr/local/etc/php/php.ini \
    && sed -i 's/memory_limit = 128M/memory_limit = 512M/' /usr/local/etc/php/php.ini \
    && sed -i 's/max_execution_time = 30/max_execution_time = 300/' /usr/local/etc/php/php.ini \
    && sed -i 's/max_input_time = 60/max_input_time = 300/' /usr/local/etc/php/php.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Create system user
RUN groupadd --force -g $WWWGROUP www \
    && useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER www

# Create required directories
RUN mkdir -p /var/log/xdebug \
    && touch /var/log/xdebug.log \
    && chown www:www /var/log/xdebug.log

# Supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Set correct permissions
RUN chown -R www:www /var/www/html

USER www

EXPOSE 9000

CMD ["php-fpm"]
