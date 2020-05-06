FROM php:7.4.3-apache

# SMTP server settings
ARG mail_host
ARG mail_tls
ARG mail_port
ARG mail_user
ARG mail_pass
ARG mail_sender

# Maximum board18 game box zip file size
ARG max_game_box_zip_file_size

RUN docker-php-ext-install mysqli

# install the PHP zip extension, which is needed for installing
# game boxes.
# see https://stackoverflow.com/questions/55511346/how-can-i-install-zip-extension-in-php5-4-apache-docker
RUN set -eux; apt-get update; apt-get install -y libzip-dev zlib1g-dev; docker-php-ext-install zip

# the board18 application.  the owner is www-data because Apache runs
# as that user and board18 needs write access to the webroot for game
# box management.
COPY --chown=www-data:www-data webroot /var/www/html/

# create/overwrite config.php with the board18 database settings.
RUN sh -c 'echo "<?php \n \
  define('\''DB_HOST'\'', '\''board18-mysql'\''); \n \
  define('\''DB_DATABASE'\'', '\''board18'\''); \n \
  define('\''DB_USER'\'', '\''board18'\''); \n \
  define('\''DB_PASSWORD'\'', '\''board18'\''); \n" > /var/www/html/php/config.php'

# create/overwrite configMail.php with SMTP server settings from file .env.
RUN sh -c 'echo "<?php \n \
  define('\''MAIL_HOST'\'', '\''${mail_host}'\''); \n \
  define('\''MAIL_TLS'\'', '\''${mail_tls}'\''); \n \
  define('\''MAIL_PORT'\'', '\''${mail_port}'\''); \n \
  define('\''MAIL_USER'\'', '\''${mail_user}'\''); \n \
  define('\''MAIL_PASS'\'', '\''${mail_pass}'\''); \n \
  define('\''MAIL_SENDER'\'', '\''${mail_sender}'\''); \n" > /var/www/html/php/configMail.php'

# set the maximum game box zip file size
RUN sh -c 'echo "upload_max_filesize = ${max_game_box_zip_file_size} \n" > "$PHP_INI_DIR/conf.d/board18-php.ini"'
