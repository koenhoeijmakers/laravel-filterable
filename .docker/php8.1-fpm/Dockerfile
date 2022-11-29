FROM php:8.1-fpm
ARG user
ARG uid

RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y \
        # utils
        nano \
        unzip \
        #zip
        libzip-dev \
        # regex
        libonig-dev \
        # curl
        pkg-config \
        libcurl4-openssl-dev \
    && pecl install xdebug \
    && docker-php-ext-install -j$(nproc) mbstring zip curl \
    && docker-php-ext-enable xdebug \
    && docker-php-source delete

# Install composer from image. You may change it to the latest
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mkdir -p /var/www

RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

WORKDIR /var/www

RUN echo 'alias phpunit="./vendor/bin/phpunit"' >> ~/.bashrc

EXPOSE 9000
CMD ["php-fpm"]
