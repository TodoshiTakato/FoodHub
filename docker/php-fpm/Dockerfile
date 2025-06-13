FROM php:8.3-fpm-alpine

# Install PHP extensions, Composer, and nano
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apk add --no-cache nano supervisor \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions bcmath pdo_pgsql pgsql redis-stable zip curl intl gd pcntl @composer

ARG user
ARG uid

# Передаём SUPERVISOR_USER через ARG и задаём как ENV
ARG SUPERVISOR_USER=$user
ENV SUPERVISOR_USER=$SUPERVISOR_USER

## Create a group and user
RUN addgroup -S $user && \
    adduser -S $user -G www-data -h /var/www/html/ -u $uid && \
    adduser $user root && \
    chown -R $user:$user /var/www/html

USER $user

# Start Supervisor (manages PHP-FPM and queue workers)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
