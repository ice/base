#
# This dockerfile was created mainly for development purposes. Some configurations are production ready, others were only
# used for development purposes.
# Feel free to reuse whatever you want, and know that your efforts to help improve this code is greatly appreciated.
#
# Kind regards,
# Daan Biesterbos <d.biesterbos@leadtech.nl>
#

#
# PURPOSE:  Nginx Webserver
# VERSION:  1.0.0
#

FROM nginx:stable as WEBSERVER

MAINTAINER Daan Biesterbos <d.biesterbos@leadtech.nl>

ENV WWW_USER=nginx
ENV WWW_GROUP=nginx
ENV WWW_DOCUMENT_ROOT=/var/www/httpdocs
ENV WWW_UID=1000
ENV WWW_GID=1000
ENV UPLOADS_URL_PATH=/media/uploads
ENV ERROR_LOG=/var/log/nginx/app_error_prod.log
ENV ERROR_DEV_LOG=/var/log/nginx/app_error_dev.log
ENV ACCESS_LOG=/var/log/nginx/app_access_prod.log
ENV ACCESS_DEV_LOG=/var/log/nginx/app_access_dev.log
ENV ACCESS_SECURITY_LOG=/var/log/nginx/app_security_access.log
ENV STATIC_FILE_ACCESS_LOG=off
ENV PHP_FPM_HOST=php_iceapp
ENV PHP_FPM_PORT=9000
ENV NGINX_HTTP_PORT=80
ENV NGINX_HTTPS_PORT=443
ENV NGINX_DOCUMENT_ROOT=/var/www/httpdocs/public
ENV NGINX_CACHE_MIN_USES=5
ENV NGINX_CACHE_NAME=app_cache
ENV NGINX_CACHE_METADATA_MEMORY_SIZE=50m
ENV NGINX_CACHE_TTL=60m
ENV NGINX_CACHE_PATH=/tmp/cache
ENV NGINX_CACHE_MAX_SIZE=2g
ENV NGINX_CACHE_USE_TEMP_PATH=off
ENV STATIC_FILE_EXTENSIONS='jpg|jpeg|gif|png|ico|css|zip|tgz|gz|rar|bz2|pdf|txt|tar|wav|bmp|rtf|js|html|xhtml'
ENV DENIED_EXTENSIONS='dist|markdown|md|twig|yaml|yml|sass|scss|zip|tar|tar.gz|rar'

RUN rm /etc/nginx/conf.d/default.conf
RUN usermod -u $WWW_UID $WWW_USER
RUN groupmod -g $WWW_GID $WWW_GROUP 2> /dev/null || usermod -a -G $WWW_GID $WWW_USER

COPY docker/conf/nginx/default.conf.template /etc/nginx/conf.d/default.conf.template
COPY docker/conf/nginx/include.fastcgi_common /etc/nginx/conf.d/include.fastcgi_common
COPY docker/conf/nginx/include.whitelist /etc/nginx/conf.d/include.whitelist

RUN mkdir -p /var/www/httpdocs/.git
# We don't want to copy .git to our container, ever. Make it a volume.
VOLUME /var/www/httpdocs/.git
WORKDIR /var/www/httpdocs
COPY . /var/www/httpdocs

RUN chown nginx:nginx /var/www/httpdocs -R

CMD /bin/bash -c "envsubst '\$UPLOADS_URL_PATH,\$ERROR_LOG, \$ERROR_DEV_LOG, \$ACCESS_LOG, \$ACCESS_DEV_LOG, \$ACCESS_SECURITY_LOG, \$STATIC_FILE_ACCESS_LOG, \$PHP_FPM_HOST, \$PHP_FPM_PORT, \$NGINX_HTTP_PORT, \$NGINX_HTTPS_PORT, \$NGINX_DOCUMENT_ROOT, \$NGINX_CACHE_MIN_USES, \$NGINX_CACHE_NAME, \$NGINX_CACHE_METADATA_MEMORY_SIZE, \$NGINX_CACHE_TTL, \$NGINX_CACHE_PATH,  \$NGINX_CACHE_MAX_SIZE,  \$NGINX_CACHE_USE_TEMP_PATH,  \$STATIC_FILE_EXTENSIONS,  \$DENIED_EXTENSIONS'  < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf && exec nginx -g 'daemon off;'"


#
# PURPOSE:  MONGODB
# VERSION:  1.0.0
#

FROM bitnami/mongodb:latest as MONGODB

MAINTAINER Daan Biesterbos <d.biesterbos@leadtech.nl>


#
# PURPOSE:  PHP-FPM
# VERSION:  1.0.0
#

FROM ubuntu:18.04 as PHP-FPM

MAINTAINER Daan Biesterbos <d.biesterbos@leadtech.nl>


# In order to run commands inside the container we'll need to set the following environment variable
# Note that we also need to provide the same value to the nginx container because PHP-FPM might not have
# access to the environment variables.
ENV SECRETS_ENV=/run/secrets/app_conf

ENV APP_ENV=prod
ENV WWW_USER=www-data
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV WWW_GROUP=www-data
ENV WWW_DOCUMENT_ROOT=/var/www/httpdocs
ENV WWW_UID=1000
ENV WWW_GID=1000
ENV DEBIAN_FRONTEND noninteractive

# Define environment variables for each directory that we commonly refer want from configurations and scripts.
ENV PHP_MODS_AVAILABLE_DIR=/etc/php/7.2/mods-available
ENV PHP_FPM_CONFD_DIR=/etc/php/7.2/fpm/conf.d
ENV PHP_CLI_CONFD_DIR=/etc/php/7.2/cli/conf.d
ENV PHP_CLI_INI=/etc/php/7.2/cli/php.ini
ENV PHP_FPM_INI=/etc/php/7.2/fpm/php.ini
ENV PHP_FPM_CONF=/etc/php/7.2/fpm/php-fpm.conf
ENV PHP_FPM_WWW_CONF=/etc/php/7.2/fpm/pool.d/www.conf

ENV TIMEZONE=UTC
ENV ICE_FRAMEWORK_VERSION=1.4.0

# Define arguments
ARG buildno
ARG gitcommithash
ARG ZEPHIR_PARSER_VERSION="1.1.4"
ARG ZEPHIR_LANG_VERSION="0.11.8"
ARG OPCACHE_EXT=1
ARG OPCACHE_EXT_VERSION=\*
ARG BCMATH_EXT=0
ARG BCMATH_EXT_VERSION=\*
ARG PROTOBUF_EXT=1
ARG SQLITE3_EXT=0
ARG SQLITE3_EXT_VERSION=\*
ARG REDIS_EXT=1
ARG PHPDBG_EXT=0
ARG PHPDBG_EXT_VERSION=\*
ARG MONGODB_EXT=1
ARG MONGODB_EXT_VERSION=1.5.3
ARG MYSQL_EXT=1
ARG MYSQL_EXT_VERSION=\*
ARG PGSQL_EXT=0
ARG PGSQL_EXT_VERSION=\*
ARG SOAP_EXT=0
ARG SOAP_EXT_VERSION=\*
ARG GD_EXT=1
ARG GD_EXT_VERSION=\*
ARG IMAP_EXT=1
ARG IMAP_EXT_VERSION=\*
ARG BZ2_EXT=0
ARG BZ2_EXT_VERSION=\*
ARG INTL_EXT=1
ARG INTL_EXT_VERSION=\*
ARG PSPELL_EXT=0
ARG PSPELL_EXT_VERSION=\*
ARG ODBC_EXT=0
ARG ODBC_EXT_VERSION=\*
ARG XMLRPC_EXT=0
ARG XMLRPC_EXT_VERSION=\*
ARG CGI_EXT=0
ARG CGI_EXT_VERSION=\*
ARG TIDEWAYS_EXT=0
ARG TIDEWAYS_EXT_VERSION=\*
ARG TIDY_EXT=1
ARG TIDY_EXT_VERSION=\*
ARG RECODE_EXT=0
ARG RECODE_EXT_VERSION=\*
ARG ZIP_EXT=1
ARG ZIP_EXT_VERSION=\*
ARG LDAP_EXT=0
ARG LDAP_EXT_VERSION=\*
ARG SOLR_EXT=1
ARG SOLR_EXT_VERSION=2.4.0
ARG INSTALL_IMAGE_OPTIMIZERS=0
ARG INSTALL_FRONTEND_TOOLS=0

# Output version info
RUN echo "Build number: $buildno"
RUN echo "Based on commit: $gitcommithash"

# Fix permissions
RUN usermod -u $WWW_UID $WWW_USER
RUN groupmod -g $WWW_GID $WWW_GROUP 2> /dev/null || usermod -a -G $WWW_GID $WWW_USER

COPY docker/conf/opcache.ini ${PHP_MODS_AVAILABLE_DIR}/opcache.user.ini
COPY docker/conf/opcache.ini ${PHP_FPM_CONFD_DIR}/opcache.user.ini
#COPY docker/conf/solr.ini ${PHP_MODS_AVAILABLE_DIR}/solr.ini

COPY docker/conf/xdebug.ini ${PHP_MODS_AVAILABLE_DIR}/xdebug.user.ini
COPY docker/conf/xdebug.ini ${PHP_FPM_CONFD_DIR}/xdebug.user.ini

RUN apt-get clean && apt-get -y update && apt-get install -y locales curl software-properties-common git apt-utils zip unzip re2c iproute2 && locale-gen en_US.UTF-8
RUN LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php

RUN apt-get update && apt-get install -y \
    php-pear \
    php7.2-dev \
    php7.2-fpm \
    net-tools \
    iputils-ping \
    iproute2  \
    php7.2-cli \
    php7.2-common \
    php7.2-curl \
    php7.2-json \
    php7.2-mbstring \
    php7.2-readline  \
    php7.2-xml \
    php7.2-xsl \
    php7.2-json \
    php7.2-dev \
    gcc \
    make \
    re2c \
    libpcre3-dev \
    build-essential \
    autoconf \
    automake

RUN sed -i -e "s/;date.timezone =.*/date.timezone = UTC/" ${PHP_CLI_INI}
RUN sed -i -e "s/;date.timezone =.*/date.timezone = UTC/" ${PHP_FPM_INI}

RUN sed -i "/daemonize /c \
daemonize = no" ${PHP_FPM_CONF}

RUN sed -i "/cgi.fix_pathinfo /c \
cgi.fix_pathinfo=1" ${PHP_FPM_CONF}

RUN sed -i "/^listen /c \
listen = 0.0.0.0:9000" ${PHP_FPM_WWW_CONF}

# Ensure worker stdout and stderr are sent to the main error log.
RUN sed -i "/^;catch_workers_output /c \
catch_workers_output = yes" ${PHP_FPM_WWW_CONF}

RUN sed -i "/^;ping.path /c \
ping.path = /ping" ${PHP_FPM_WWW_CONF}

RUN sed -i "/^;ping.response /c \
ping.response = pong" ${PHP_FPM_WWW_CONF}


VOLUME /var/www/httpdocs

RUN sed -i "/^;chdir /c \
chdir = /var/www/httpdocs" ${PHP_FPM_WWW_CONF}

# clear_env = no

RUN sed -i "/^upload_max_filesize /c \
upload_max_filesize = 5M" ${PHP_FPM_INI}

RUN sed -i "/^post_max_size /c \
post_max_size = 5M" ${PHP_FPM_INI}

RUN sed -i "/^memory_limit /c \
memory_limit = 1024M" ${PHP_CLI_INI}

RUN sed -i "/^max_execution_time /c \
max_execution_time = -1" ${PHP_CLI_INI}


RUN sed -i -e "s/;catch_workers_output =.*/catch_workers_output = yes/" ${PHP_FPM_WWW_CONF}
RUN sed -i -e "s/;clear_env =.*/clear_env = no/" ${PHP_FPM_WWW_CONF}
RUN sed -i -e "s/pid =.*/pid = \/var\/run\/php7.2-fpm.pid/" ${PHP_FPM_CONF}

#####################################
# APP ENVIRONMENT (dev, prod, ...)
#####################################

RUN if [ ${APP_ENV} = "dev" ]; then \
    sed -i -e "s/display_errors =.*/display_errors = On/" ${PHP_FPM_INI} \
    && apt-get install -y php7.2-xdebug \
;else \
    sed -i "s/display_errors = .*/display_errors = Off/" ${PHP_FPM_INI} \
    && rm ${PHP_MODS_AVAILABLE_DIR}/xdebug.user.ini \
    && rm ${PHP_FPM_CONFD_DIR}/xdebug.user.ini \
;fi


USER root

#####################################
# INSTALL PHP EXTENSIONS
#####################################

RUN if [ ${INTL_EXT} = 1 ]; then \
    apt-get install -y php7.2-intl=${INTL_EXT_VERSION} \
;fi

RUN if [ ${BZ2_EXT} = 1 ]; then \
    apt-get install -y php7.2-bz2=${BZ2_EXT_VERSION} \
;fi

RUN if [ ${PSPELL_EXT} = 1 ]; then \
    apt-get install -y php7.2-pspell=${PSPELL_EXT_VERSION} \
;fi

RUN if [ ${GD_EXT} = 1 ]; then \
    apt-get install -y php7.2-gd=${GD_EXT_VERSION} \
;fi

RUN if [ ${IMAP_EXT} = 1 ]; then \
    apt-get install -y php7.2-imap=${IMAP_EXT_VERSION} \
;fi
RUN if [ ${CGI_EXT} = 1 ]; then \
    apt-get install -y php7.2-cgi=${CGI_EXT_VERSION} \
;fi

RUN if [ ${XMLRPC_EXT} = 1 ]; then \
    apt-get install -y php7.2-xmlrpc=${XMLRPC_EXT_VERSION} \
;fi

RUN if [ ${SOAP_EXT} = 1 ]; then \
    apt-get install -y php7.2-soap=${SOAP_EXT_VERSION} \
;fi

RUN if [ ${ZIP_EXT} = 1 ]; then \
    apt-get install -y php7.2-zip=${ZIP_EXT_VERSION} \
;fi

RUN if [ ${RECODE_EXT} = 1 ]; then \
    apt-get install -y php7.2-recode=${RECODE_EXT_VERSION} \
;fi

RUN if [ ${PGSQL_EXT} = 1 ]; then \
    apt-get install -y php7.2-pgsql=${PGSQL_EXT_VERSION} \
;fi

RUN if [ ${TIDY_EXT} = 1 ]; then \
    apt-get install -y php7.2-tidy=${TIDY_EXT_VERSION} \
;fi

RUN if [ ${ODBC_EXT} = 1 ]; then \
    apt-get install -y php7.2-odbc=${ODBC_EXT_VERSION} \
;fi

RUN if [ ${LDAP_EXT} = 1 ]; then \
    apt-get install -y php7.2-ldap=${LDAP_EXT_VERSION}  \
;fi

RUN if [ ${PHPDBG_EXT} = 1 ]; then \
    apt-get install -y php7.2-phpdbg=${PHPDBG_EXT_VERSION}  \
;fi

RUN if [ ${MYSQL_EXT} = 1 ]; then \
    apt-get install -y php7.2-mysql=${MYSQL_EXT_VERSION} \
;fi

RUN if [ ${TIDEWAYS_EXT} = 1 ]; then \
    apt-get install -y php-tideways=${TIDEWAYS_EXT_VERSION} \
;fi

RUN if [ ${MONGODB_EXT} = 1 ]; then \
    apt-get install -y php-mongodb=${SQLITE3_EXT_VERSION} --fix-missing  \
;fi

RUN if [ ${SOLR_EXT} = 1 ]; then \
    apt-get install -y php-solr \
;fi

RUN if [ ${PROTOBUF_EXT} = 1 ]; then \
   pecl install -o -f protobuf \
;fi

RUN if [ ${SQLITE3_EXT} = 1 ]; then \
    apt-get install -y php7.2-sqlite3=${SQLITE3_EXT_VERSION} \
;fi

RUN if [ ${BCMATH_EXT} = 1 ]; then \
    apt-get install -y php7.2-bcmath=${BCMATH_EXT_VERSION} \
;fi

RUN if [ ${REDIS_EXT} = 1 ]; then \
    pecl install redis \
;fi

RUN if [ ${OPCACHE_EXT} = 1 ]; then \
    apt-get install -y php7.2-opcache=${OPCACHE_EXT_VERSION} \
;else \
    rm ${PHP_MODS_AVAILABLE_DIR}/opcache.user.ini \
    rm ${PHP_FPM_CONFD_DIR}/opcache.user.ini \
;fi

ENV INSTALL_IMAGE_OPTIMIZERS ${INSTALL_IMAGE_OPTIMIZERS}
RUN if [ ${INSTALL_IMAGE_OPTIMIZERS} = 1 ]; then \
    apt-get update -yqq && apt-get install -y jpegoptim optipng pngquant gifsicle \
;fi

#####################################
# INSTALL TOOLS & UTILS
#####################################

RUN if [ ${INSTALL_FRONTEND_TOOLS} = 1 ]; then \
    apt-get update && apt-get install -my wget gnupg \
    && curl -sL https://deb.nodesource.com/setup_6.x | bash - \
    && apt-get install -y  nodejs \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install yarn \
    && yarn global add gulp \
;fi

RUN curl -sS https://getcomposer.org/installer -o composer-setup.php && php composer-setup.php --install-dir=/usr/bin --filename=composer

# Install zephir language, zephir parser and dependencies
RUN pecl install ds \
    && pecl install psr \
    && echo "extension=ds.so" >  ${PHP_MODS_AVAILABLE_DIR}/ds.ini \
    && echo "extension=ds.so" >  ${PHP_FPM_CONFD_DIR}/ds.ini \
    && echo "extension=ds.so" >  ${PHP_CLI_CONFD_DIR}/ds.ini \
    && echo "extension=psr.so" >  ${PHP_MODS_AVAILABLE_DIR}/psr.ini \
    && echo "extension=psr.so" >  ${PHP_FPM_CONFD_DIR}/psr.ini \
    && echo "extension=psr.so" >  ${PHP_CLI_CONFD_DIR}/psr.ini \
    && cd / && curl -sSL "https://github.com/phalcon/php-zephir-parser/archive/v${ZEPHIR_PARSER_VERSION}.tar.gz" | tar -xz \
    && cd php-zephir-parser-${ZEPHIR_PARSER_VERSION} \
    && /usr/bin/phpize7.2 \
    && ./configure --with-php-config=/usr/bin/php-config7.2 \
    && make \
    && make install \
    && echo "extension=zephir_parser.so" >  ${PHP_MODS_AVAILABLE_DIR}/zephir_parser.ini \
    && echo "extension=zephir_parser.so" >  ${PHP_FPM_CONFD_DIR}/zephir_parser.ini \
    && echo "extension=zephir_parser.so" >  ${PHP_CLI_CONFD_DIR}/zephir_parser.ini \
    && cd / && git clone https://github.com/phalcon/zephir \
    && cd zephir/ext \
    && export CC="gcc" \
    && export CFLAGS="-O2 -Wall -fvisibility=hidden -flto -DZEPHIR_RELEASE=1" \
    && make -s clean \
    && phpize7.2 --silent \
    && ./configure --with-php-config=/usr/bin/php-config7.2 --silent --enable-test \
    && make -s && make -s install \
    && apt-get install -y php-gmp php7.2-sqlite curl php-mbstring git unzip \
    && cd ../ \
    && composer install \
    && make test  \
    && ln -s /zephir/zephir /usr/bin/zephir


#####################################
# PACKAGE SOURCE CODE (NO VOLUME IN PROD)
#####################################

RUN mkdir -p /var/www/httpdocs/.git
# We don't want to copy .git to our container, ever. Make it a volume.
VOLUME /var/www/httpdocs/.git
WORKDIR /var/www/httpdocs
COPY . /var/www/httpdocs

RUN chown www-data:www-data /var/www/httpdocs -R

#####################################
# CLEANUP
#####################################

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


#####################################
# DOCKER CONTAINER CONFIGS
#####################################

EXPOSE 9000

COPY docker/entrypoint_runall.sh /usr/bin/docker-entrypoint.sh
COPY docker/bin/iceframework.sh /opt/docker/provision/entrypoint.d/iceframework.sh
COPY docker/bin/permissions.sh /opt/docker/provision/entrypoint.d/permissions.sh


RUN chmod +x /usr/bin/docker-entrypoint.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/*
ENTRYPOINT ["/usr/bin/docker-entrypoint.sh"]

CMD ["php-fpm7.2"]
