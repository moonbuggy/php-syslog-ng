FROM smebberson/alpine-nginx:3.0.0

ARG BUILD_DATE
LABEL org.label-schema.schema-version = "1.0"
LABEL org.label-schema.build-date = $BUILD_DATE
LABEL org.label-schema.version = "1.0"
LABEL org.label-schema.url = "https://github.com/moonbuggy/php-syslog-ng"
LABEL org.label-schema.vcs-ref = "https://github.com/moonbuggy/php-syslog-ng"
LABEL org.label-schema.description = "Updated version of php-syslog-ng in a Docker container."
LABEL org.label-schema.name = "php-syslog-ng"

# Install PHP
RUN \
	apk add --no-cache --progress \
		curl \
		ssmtp \ 
		php5-fpm \
		php5-mysql \
		php5-gd \
		php5-ldap \
		php5-xmlrpc \
		php5-zlib

COPY container-root /

RUN \
       #autorestart php-fpm by S6 in case of crash
       ln -s /bin/true /etc/services.d/php/finish && \
       #to output logging info to Docker-Logs
       ln -sf /dev/stdout /var/log/nginx/access.log && \
       ln -sf /dev/stderr /var/log/nginx/error.log

VOLUME /var/www/app

EXPOSE 80 443

