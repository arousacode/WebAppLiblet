################
## Service for running landra web app, moodle and squid
##################
# /landra/intranet will be connected through volumes with landra code
# /var/www/moodle will be a named volume to store and preserve moodle source and file data
# /init_moodle will be connected through volume top init_moodle to load initial moodle source and file data
#

FROM php:8.1-apache
RUN apt-get update
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get install -y  postgresql-server-dev-all  
RUN docker-php-ext-install pdo pdo_pgsql 
RUN pecl install apcu
RUN docker-php-ext-enable  apcu

