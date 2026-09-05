# Usa un'immagine base di PHP con FPM
FROM php:8.3-fpm

# Installa le dipendenze richieste
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev libzip-dev default-mysql-client nano \
    libicu-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo_mysql intl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Timezone
RUN ln -sf /usr/share/zoneinfo/Europe/Rome /etc/localtime && dpkg-reconfigure -f noninteractive tzdata

# Installa Composer ultima versione
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Crea la cartella laravel dentro /var/www/html e imposta la directory di lavoro
RUN mkdir -p /var/www/html
WORKDIR /var/www/html

# Pulisce la cartella prima di creare il progetto Laravel
RUN rm -rf /var/www/html/*

# Comando di avvio per PHP-FPM
CMD php-fpm

# Espone la porta 9000 per PHP-FPM
EXPOSE 9000

