#!/bin/bash
/usr/bin/mysql -uroot -pArie99% -e'use syslog; delete from search_cache;'
/usr/bin/php /var/www/scripts/reloadcache.php >> /var/log/logzilla/reloadcache.log

