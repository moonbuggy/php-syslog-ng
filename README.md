# php-syslog-ng

A Docker image for [php-syslog-ng][OpenAai/php-syslog-ng], served by [smebberson's alpine-nginx][alpine-nginx].

Based on [GlebPoljakov's alpine-nginx-php5][alpine-nginx-php5], with unnecessary modules removed and php-syslog-ng added.

## Usage

``docker run -p 80:80 --name php-syslog-ng moonbuggy/php-syslog-ng``

Once the container is started initial configuration is done at ``http://<host>/install/``.

The image does not include syslog-ng, just the web frontend for viewing data from a syslog-ng SQL database.

### Persisting configuration

Mount ``/var/www/app/config/config.php`` to retain configuration. This can also be used to cut and paste an existing configuration, if you want to bypass the installation.

## Caveat emptor

This hasn't been particularly thoroughly tested, I just wanted a quick and dirty web frontend. 

[php-syslog-ng][OpenAai/php-syslog-ng] is no longer developed, the creator went on to develop [Logzilla][logzilla] as an alternative.

This image is not compatible with ARM architectures due to ARM not (currently) being supported by images it's based on, although there's no reason it couldn't be made compatible (there's nothing in this layer that creates an incompatibility).

[OpenAai/php-syslog-ng]: https://github.com/OpenAai/php-syslog-ng
[alpine-nginx-php5]: https://github.com/GlebPoljakov/docker-alpine-nginx-php5
[alpine-nginx]: https://github.com/smebberson/docker-alpine/tree/master/alpine-nginx
[logzilla]: https://www.logzilla.net/
