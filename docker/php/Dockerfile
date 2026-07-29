FROM php:8.5-fpm-trixie

ARG USER_ID
ARG GROUP_ID

# Remove and create the user www-data with the same user id as the host system (Linux requirement)
RUN if [ ${USER_ID:-0} -ne 0 ] && [ ${GROUP_ID:-0} -ne 0 ]; then \
        userdel -f www-data \
        && if getent group www-data ; then groupdel www-data; fi \
        && groupadd -g ${GROUP_ID} www-data \
        && useradd -l -u ${USER_ID} -g www-data -d /home/www-data -s /usr/sbin/nologin www-data \
        && install -d -m 0755 -o www-data -g www-data /home/www-data \
        && chown --changes --silent --no-dereference --recursive --from=33:33 ${USER_ID}:${GROUP_ID} /var/www \
    ;fi

# Install install-php-extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Install required system packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install required extensions
RUN MAKEFLAGS="-j$(nproc)" install-php-extensions xml zip xdebug

# Configure Xdebug
RUN echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/xdebug.ini

# Copy the custom php ini
COPY ./conf.d/app-php.ini $PHP_INI_DIR/conf.d/

# Change the curreny user to www-data
USER www-data

# Create aliases
RUN echo 'alias sc="bin/console"' >> ~/.bashrc

# Set the working directory
WORKDIR /var/www
