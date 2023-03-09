FROM docker.io/bitnami/laravel:10
RUN apt update && apt -y install g++ make autoconf
RUN pecl install apcu

RUN echo "extension=apcu.so" >> /opt/bitnami/php/lib/php.ini
