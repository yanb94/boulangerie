FROM php:7.4-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libyaml-dev \
    libzip4 \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libmagickwand-dev \
    g++ \
    git \
    cron \
    vim \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install apcu \
    && pecl install yaml-2.0.4 \
    && docker-php-ext-enable apcu yaml

RUN echo "$(curl -sS https://composer.github.io/installer.sig) -" > composer-setup.php.sig \
        && curl -sS https://getcomposer.org/installer | tee composer-setup.php | sha384sum -c composer-setup.php.sig \
        && php composer-setup.php && rm composer-setup.php* \
        && chmod +x composer.phar && mv composer.phar /usr/bin/composer

RUN apt-get install -y curl \
  && curl -sL https://deb.nodesource.com/setup_15.x | bash - \
  && apt-get install -y nodejs \
  && curl -L https://www.npmjs.com/install.sh | sh

RUN npm install -g sass

COPY ./opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN useradd yanb -m
USER yanb

RUN echo 'alias compile-sass="sass --source-map --watch /var/www/html/user/themes/boulangerie/scss:/var/www/html/user/themes/boulangerie/css-compiled --style compressed"' >> ~/.bashrc
RUN git config --global user.email "20875343+yanb94@users.noreply.github.com" \
  && git config --global user.name "Yann-Elias BELLAGNECH"


WORKDIR /var/www/html