
FROM php:8.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev nano \
    ca-certificates gnupg dirmngr libicu-dev procps \
    libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_pgsql intl zip pcntl sockets gd \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set timezone
RUN ln -sf /usr/share/zoneinfo/Europe/Rome /etc/localtime && dpkg-reconfigure -f noninteractive tzdata

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Laravel Installer
RUN composer global require laravel/installer

# Add composer to PATH
ENV PATH="/root/.composer/vendor/bin:${PATH}"

# Create application folder
RUN mkdir -p /var/www/html
WORKDIR /var/www/html

# Cleanup
RUN rm -rf /var/www/html/*

# Expose port
EXPOSE 9000

CMD ["php-fpm"]