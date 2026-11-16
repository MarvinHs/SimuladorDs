FROM php:8.2-apache

# La imagen oficial ya incluye curl, json, mbstring y session.
# Mostrar errores en el log de Docker, no en la respuesta HTTP (rompería el JSON).
# También permite que .htaccess proteja config/, src/ y archivos ocultos.
RUN { \
        echo 'display_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_log = /dev/stderr'; \
        echo 'max_execution_time = 90'; \
        echo 'expose_php = Off'; \
        echo 'session.use_strict_mode = 1'; \
    } > /usr/local/etc/php/conf.d/app.ini \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override \
    && a2enmod headers rewrite \
    && echo 'ServerTokens Prod' >> /etc/apache2/conf-available/security.conf \
    && echo 'ServerSignature Off' >> /etc/apache2/conf-available/security.conf

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .

EXPOSE 80
