#!/bin/bash
/usr/bin/mysql -uroot -pPASSWORD_ROOT_USER -e'use syslog; delete from search_cache;'
/usr/bin/php /var/www/scripts/reloadcache.php >> /var/log/logzilla/reloadcache.log

