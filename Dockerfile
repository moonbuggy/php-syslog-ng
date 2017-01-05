FROM smebberson/alpine-nginx:3.0.0
MAINTAINER Gleb Poljakov <gleb.poljakov@gmail.com>

ARG BOOKED_SOURCE=http://downloads.sourceforge.net/project/phpscheduleit/Booked/2.6/booked-2.6.3.zip
ARG HTML_DIR=/var/www/app
ENV MYSQL_SERVER mysql

# Install PHP
RUN apk add --update curl ssmtp \ 
	php5-fpm php5-mcrypt php5-soap php5-openssl php5-gmp php5-pdo_odbc php5-json php5-dom php5-pdo php5-zip php5-mysql php5-mysqli php5-sqlite3 php5-apcu php5-pdo_pgsql php5-bcmath php5-gd php5-xcache php5-odbc php5-pdo_mysql php5-pdo_sqlite php5-gettext php5-xmlreader php5-xmlrpc php5-bz2 php5-memcache php5-mssql php5-iconv php5-pdo_dblib php5-curl php5-ctype \
	rm -rf /var/cache/apk/*

#Configure PHP:
ARG PHP_FPM_USER="www"
ARG PHP_FPM_GROUP="www"
ARG PHP_FPM_LISTEN_MODE="0660"
ARG PHP_MEMORY_LIMIT="512M"
ARG PHP_MAX_UPLOAD="50M"
ARG PHP_MAX_FILE_UPLOAD="200"
ARG PHP_MAX_POST="100M"
ARG PHP_DISPLAY_ERRORS="On"
ARG PHP_DISPLAY_STARTUP_ERRORS="On"
ARG PHP_ERROR_REPORTING="E_COMPILE_ERROR\|E_RECOVERABLE_ERROR\|E_ERROR\|E_CORE_ERROR" 
ARG PHP_CGI_FIX_PATHINFO=0

RUN \
	#php-fpm.conf
	sed -i "s|;listen.owner\s*=\s*nobody|listen.owner = ${PHP_FPM_USER}|g" /etc/php5/php-fpm.conf \
	sed -i "s|;listen.group\s*=\s*nobody|listen.group = ${PHP_FPM_GROUP}|g" /etc/php5/php-fpm.conf \ 
	sed -i "s|;listen.mode\s*=\s*0660|listen.mode = ${PHP_FPM_LISTEN_MODE}|g" /etc/php5/php-fpm.conf \
	sed -i "s|user\s*=\s*nobody|user = ${PHP_FPM_USER}|g" /etc/php5/php-fpm.conf \
	sed -i "s|group\s*=\s*nobody|group = ${PHP_FPM_GROUP}|g" /etc/php5/php-fpm.conf \
	sed -i "s|;log_level\s*=\s*notice|log_level = notice|g" /etc/php5/php-fpm.conf #uncommenting line \
	#php.ini
	sed -i "s|display_errors\s*=\s*Off|display_errors = ${PHP_DISPLAY_ERRORS}|i" /etc/php5/php.ini \
	sed -i "s|display_startup_errors\s*=\s*Off|display_startup_errors = ${PHP_DISPLAY_STARTUP_ERRORS}|i" /etc/php5/php.ini \
	sed -i "s|error_reporting\s*=\s*E_ALL & ~E_DEPRECATED & ~E_STRICT|error_reporting = ${PHP_ERROR_REPORTING}|i" /etc/php5/php.ini \
	sed -i "s|;*memory_limit =.*|memory_limit = ${PHP_MEMORY_LIMIT}|i" /etc/php5/php.ini \
	sed -i "s|;*upload_max_filesize =.*|upload_max_filesize = ${PHP_MAX_UPLOAD}|i" /etc/php5/php.ini \
	sed -i "s|;*max_file_uploads =.*|max_file_uploads = ${PHP_MAX_FILE_UPLOAD}|i" /etc/php5/php.ini \
	sed -i "s|;*post_max_size =.*|post_max_size = ${PHP_MAX_POST}|i" /etc/php5/php.ini \
	sed -i "s|;*cgi.fix_pathinfo=.*|cgi.fix_pathinfo= ${PHP_CGI_FIX_PATHINFO}|i" /etc/php5/php.ini

#Configure Nginx: 
RUN \
	#to output logging info to Docker-Logs
	ln -sf /dev/stdout /var/log/nginx/access.log && \
	ln -sf /dev/stderr /var/log/nginx/error.log \
	#autorestart nginx by S6 in case of crash
	ln -s /bin/true /etc/services.d/nginx/finish

# copy sources to web dir 
#RUN mkdir -p ${HTML_DIR} && \
#    curl -SL ${BOOKED_SOURCE} | \
#    tar -zx -C ${HTML_DIR}/ --strip-components=1

ADD booked ${HTML_DIR}

#COPY docker/kanboard/config.php /var/www/app/config.php
#COPY docker/crontab/cronjob.alpine /var/spool/cron/crontabs/nginx
#COPY docker/services.d/cron /etc/services.d/cron
#COPY docker/php/env.conf /etc/php7/php-fpm.d/env.conf

# Configure Booked-App. Use system environment variables in config.php
RUN mv ${HTML_DIR}/config/config.dist.php ${HTML_DIR}/config/config.php && \
    sed -i \ 
    -e "s/\['host'\] = \"localhost\"/\['host'\] = \"${MYSQL_SERVER}\"/" \ 
    ${HTML_DIR}/config/config.php && \
    chown -R nginx:nginx ${HTML_DIR}

#RUN cd /var/www/app && composer --prefer-dist --no-dev --optimize-autoloader --quiet install

VOLUME /var/www/app/config
VOLUME /var/www/app/plugins

EXPOSE 80 443
