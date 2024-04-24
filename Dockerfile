FROM php:8.1.0-apache
WORKDIR /var/www/html

RUN echo "max_execution_time=30000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN a2enmod headers \
    && sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Origin "*"\n\1\2/g' /etc/apache2/sites-available/*.conf
ENV TERM xterm

# Could not reliably determine the server's fully qualified domain name, using 172.17.0.2. Set the 'ServerName' directive globally to suppress this message
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN service apache2 restart    

# EXPOSE 80
EXPOSE 443

# Copia los archivos del proyecto y restaura las dependencias
# COPY . . /var/www/html/apirest-laravellumen/
COPY . .

# Mod Rewrite
RUN a2enmod rewrite

# Linux Library
RUN apt-get update -y && apt-get install -y \
    libicu-dev \
    libmariadb-dev \
    unzip zip \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev 

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install

# PHP Extension
RUN docker-php-ext-install gettext intl gd mysqli pdo pdo_mysql

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

CMD ["apache2-foreground"]



# FROM php:8.1-fpm
# WORKDIR /var/www/html

# RUN echo "max_execution_time=3500" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# # EXPOSE 80
# EXPOSE 9000

# # Copia los archivos del proyecto y restaura las dependencias
# # COPY . . /var/www/html/apirest-laravellumen/
# COPY . .

# # Mod Rewrite
# # RUN a2enmod rewrite

# # Linux Library
# RUN apt-get update -y && apt-get install -y \
#     libicu-dev \
#     libmariadb-dev \
#     unzip zip \
#     zlib1g-dev \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#     libjpeg62-turbo-dev \
#     libpng-dev \
#     cron 

# # Composer
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# RUN composer install

# # PHP Extension
# RUN docker-php-ext-install gettext intl gd mysqli pdo pdo_mysql

# RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
#     && docker-php-ext-install -j$(nproc) gd


# # COPY crontab /etc/crontabs/root

# # CMD ["crond", "-f"]
# CMD ["php", "artisan", "schedule:work"]
# # CMD ["apache2-foreground"]

# # cd /var/www/html && php artisan schedule:run >> /dev/stdout 2>&1
# # php /var/www/html/artisan schedule:run >> /var/log/cron.log 2>&1
# # RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/stdout 2>&1"

# # Create the log file to be able to run tail
# # RUN touch /var/log/cron.log
# # CMD cron && tail -f /var/log/cron-log
# # RUN php artisan schedule:run
# # RUN php artisan schedule:work