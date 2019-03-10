FROM glebpoljakov/alpine-nginx-php5

COPY www/html/ /var/www/app/
COPY config/fix-attrs.d/01-html-path /etc/fix-attrs.d/

RUN \
	apk add --no-cache --progress php5-zlib && \
	sed -i "s|;session.save_path|session.save_path|i" /etc/php5/php.ini && \
	sed -i "s|zlib.output_compression = Off|zlib.output_compression = On|i" /etc/php5/php.ini && \
	sed -i "s|max_execution_time = 30|max_execution_time = 60|i" /etc/php5/php.ini

