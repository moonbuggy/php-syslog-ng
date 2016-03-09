# http://nms.gdd.net/index.php/LogZilla_Installation_Guide#Logrotate
# LogZilla logrotate snippet for Ubuntu Linux
# contributed by Clayton Dukes
#

/var/log/logzilla/*.log {
  missingok
  compress
  rotate 5
  daily
  postrotate
  /etc/init.d/syslog-ng reload > /dev/null 2>&1 || true
  endscript
}
