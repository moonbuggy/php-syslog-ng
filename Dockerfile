FROM smebberson/alpine-nginx:3.0.0
MAINTAINER Gleb Poljakov <gleb.poljakov@gmail.com>

# Install PHP
RUN \
	apk add --update --no-cache --progress \
		curl \
		ssmtp \ 
		php5-fpm \
		php5-mcrypt \
		php5-soap \
		php5-openssl \
		php5-gmp \
		php5-pdo_odbc \
		php5-json \
		php5-dom \
		php5-pdo \
		php5-zip \
		php5-mysql \
		php5-mysqli \
		php5-sqlite3 \
		php5-apcu \
		php5-pdo_pgsql \
		php5-bcmath \
		php5-gd \
		php5-xcache \
		php5-odbc \
		php5-pdo_mysql \
		php5-pdo_sqlite \
		php5-gettext \
		php5-xmlreader \
		php5-xmlrpc \
		php5-bz2 \
		php5-memcache \
		php5-mssql \
		php5-iconv \
		php5-pdo_dblib \
		php5-curl \
		php5-ctype && \ 
	rm -rf /var/cache/apk/*

#Configure PHP:
ARG PHP_FPM_USER="nginx"
ARG PHP_FPM_GROUP="www-data"
ARG PHP_FPM_LISTEN_MODE="0660"
ARG PHP_FPM_LISTEN_TO="/var/run/php-fpm.sock"
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
	sed -i "s|;listen.owner\s*=\s*nobody|listen.owner = ${PHP_FPM_USER}|g" /etc/php5/php-fpm.conf && \
	sed -i "s|;listen.group\s*=\s*nobody|listen.group = ${PHP_FPM_GROUP}|g" /etc/php5/php-fpm.conf && \
	sed -i "s|;listen.mode\s*=\s*0660|listen.mode = ${PHP_FPM_LISTEN_MODE}|g" /etc/php5/php-fpm.conf && \
	sed -i "s|user\s*=\s*nobody|user = ${PHP_FPM_USER}|g" /etc/php5/php-fpm.conf && \
	sed -i "s|group\s*=\s*nobody|group = ${PHP_FPM_GROUP}|g" /etc/php5/php-fpm.conf && \
	sed -i "s|listen\s*=\s*127.0.0.1:9000|listen = ${PHP_FPM_LISTEN_TO}|g" /etc/php5/php-fpm.conf && \
	sed -i "s|;log_level\s*=\s*notice|log_level = notice|g" /etc/php5/php-fpm.conf #uncommenting line && \
	#php.ini
	sed -i "s|display_errors\s*=\s*Off|display_errors = ${PHP_DISPLAY_ERRORS}|i" /etc/php5/php.ini && \
	sed -i "s|display_startup_errors\s*=\s*Off|display_startup_errors = ${PHP_DISPLAY_STARTUP_ERRORS}|i" /etc/php5/php.ini && \
	sed -i "s|error_reporting\s*=\s*E_ALL & ~E_DEPRECATED & ~E_STRICT|error_reporting = ${PHP_ERROR_REPORTING}|i" /etc/php5/php.ini && \
	sed -i "s|;*memory_limit =.*|memory_limit = ${PHP_MEMORY_LIMIT}|i" /etc/php5/php.ini && \
	sed -i "s|;*upload_max_filesize =.*|upload_max_filesize = ${PHP_MAX_UPLOAD}|i" /etc/php5/php.ini && \
	sed -i "s|;*max_file_uploads =.*|max_file_uploads = ${PHP_MAX_FILE_UPLOAD}|i" /etc/php5/php.ini && \
	sed -i "s|;*post_max_size =.*|post_max_size = ${PHP_MAX_POST}|i" /etc/php5/php.ini && \
	sed -i "s|;*cgi.fix_pat hinfo=.*|cgi.fix_pathinfo= ${PHP_CGI_FIX_PATHINFO}|i" /etc/php5/php.ini && \
	#autorestart php-fpm by S6 in case of crash
       ln -s /bin/true /etc/services.d/php/finish


#Configure Nginx: 
RUN \
       #to output logging info to Docker-Logs
       ln -sf /dev/stdout /var/log/nginx/access.log && \
       ln -sf /dev/stderr /var/log/nginx/error.log

COPY container-root /

COPY www/html/ /var/www/app/
COPY config/fix-attrs.d/01-html-path /etc/fix-attrs.d/

RUN \
        apk add --no-cache --progress php5-zlib && \
        sed -i "s|;session.save_path|session.save_path|i" /etc/php5/php.ini && \
        sed -i "s|zlib.output_compression = Off|zlib.output_compression = On|i" /etc/php5/php.ini && \
        sed -i "s|max_execution_time = 30|max_execution_time = 60|i" /etc/php5/php.ini


VOLUME /var/www/app

EXPOSE 80 443

