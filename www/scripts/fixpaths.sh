#!/bin/sh

#
# fixpaths.sh
#
# Developed by Clayton Dukes <cdukes@cdukes.com>
# Copyright (c) 2009 gdd.net
# Licensed under terms of GNU General Public License.
# All rights reserved.
#
# Changelog:
# 2009-09-14 - created
# 2009-10-12 - Simplified by creating an ABSPATH variable in config.php during install
# 2009-10-22 - Added an error check to make sure ABSPATH exists in config.php before running
#

# Run this script after install to set all paths to your installation directory


CONFIG="../html/config/config.php"
FILESIZE=$(stat -c%s "$CONFIG")
   	if [ "$FILESIZE" -gt "1" ]
	then
   	ABSPATH=`cat $CONFIG | grep ABSPATH | awk -F"'" '{print $4}'`
	if [ -n "$ABSPATH" ]
	then
	ABSPATH=`echo $ABSPATH | sed -s 's/\/html//g'`
	echo "Updating all files with a base path of $ABSPATH"
   	for file in `grep -Rl path_to_logzilla ../* | grep -v "\.svn" | grep -v fixpaths.sh`
   	do
	   	echo "Modifying $file"
   		perl -i -pe "s|/path_to_logzilla|$ABSPATH|g" $file
   	done
else
	echo "The ABSPATH variable is missing from your config.php, please set it prior to running this file"
	echo "It should be the first line in your config.php and look something like this:"
	echo "define('ABSPATH', '/www/logzilla/html' );"
	exit
fi
else
	echo "Please run the Web-based install before executing this file!"
	echo "Also note that this script MUST be run from the scripts/ directory"
	exit
fi
