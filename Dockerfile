FROM ubuntu:16.04

MAINTAINER Oscar Fanelli <oscar.fanelli@gmail.com>

# Locale generator
RUN locale-gen en_US.UTF-8

ENV PROJECT_PATH=/var/www \
    DEBIAN_FRONTEND=noninteractive \
    LANG=en_US.UTF-8 \
    LANGUAGE=en_US:en \
    LC_ALL=en_US.UTF-8 \
    PHP_MODS_CONF=/etc/php/7.0/mods-available \
    PHP_INI=/etc/php/7.0/cli/php.ini \
    TERM=xterm

# Utilities, Apache, PHP, and supplementary programs
RUN apt-get update -q && apt-get upgrade -yqq && apt-get install -yqq --force-yes \
    curl \
    git \
    php

# PHP.ini file: enable <? ?> tags and quiet logging
RUN sed -i "s/short_open_tag = .*/short_open_tag = On/" $PHP_INI && \
    sed -i "s/memory_limit = .*/memory_limit = 256M/" $PHP_INI && \
    sed -i "s/display_errors = .*/display_errors = Off/" $PHP_INI && \
    sed -i "s/display_startup_errors = .*/display_startup_errors = Off/" $PHP_INI && \
    sed -i "s/post_max_size = .*/post_max_size = 64M/" $PHP_INI && \
    sed -i "s/upload_max_filesize = .*/upload_max_filesize = 64M/" $PHP_INI && \
    sed -i "s/max_file_uploads = .*/max_file_uploads = 100/" $PHP_INI && \
    sed -i "s/error_reporting = .*/error_reporting = E_ALL \& ~E_DEPRECATED \& ~E_STRICT/" $PHP_INI

# Cleanup
RUN apt-get autoremove -yqq

# Port to expose
EXPOSE 80

# Move composer before copy project, in order to improve docker cache
WORKDIR $PROJECT_PATH
COPY composer.json $PROJECT_PATH/composer.json
COPY composer.lock $PROJECT_PATH/composer.lock

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --optimize-autoloader

# Copy site into place
COPY . $PROJECT_PATH
WORKDIR $PROJECT_PATH

# Make it running
CMD /bin/bash