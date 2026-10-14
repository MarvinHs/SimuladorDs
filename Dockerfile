FROM php:8.2-apache

# La imagen oficial ya incluye curl, json, mbstring y session.
# Mostrar errores en el log de Docker, no en la respuesta HTTP (rompería el JSON).
RUN { \
        echo 'display_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_log = /dev/stderr'; \
        echo 'max_execution_time = 60'; \
        echo 'upload_max_filesize = 10M'; \
        echo 'post_max_size = 12M'; \
    } > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html
COPY . .

RUN mkdir -p uploads \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
