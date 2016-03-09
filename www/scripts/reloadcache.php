#!/usr/bin/php
<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
echo "\nStarting to reload cache\n";
echo date("Y-m-d H:i:s")."\n\n";

require_once "/var/www/html/includes/common_funcs.php";
require_once "/var/www/html/config/config.php";

$file = fopen('/tmp/reloadcache.lock', 'w');

if(flock($file, LOCK_EX | LOCK_NB)){
	   echo "Got lock, continue.\n";
}else{
	   echo "File is locked by another process, aborting.\n";
	      exit(1);
}

$dbLink = db_connect_syslog(DBUSER, DBUSERPW);

// If merge table exists and is not empty
// then load the cache with data from that table

if(table_exists(MERGELOGTABLE, $dbLink) == TRUE ) {
	$mergelog = TRUE;
	$sql = "SELECT * FROM ".MERGELOGTABLE." LIMIT 1";
	$result = perform_query($sql, $dbLink);
	if(num_rows($result)) {
	echo "Loading the cache with data from the merge table\n";
	reload_cache(MERGELOGTABLE, $dbLink);
	}
} else {
// Else load the cache with data from each log table
	$tableArray = get_logtables($dbLink);
	foreach($tableArray as $table) {
		if ($table == MERGELOGTABLE) {
			continue;
		}
		echo "Loading the cache with data from: ".$table."\n";
		reload_cache($table, $dbLink);
	}
 }

// Delete rows with data from log tables that do not exist
echo "\nDeleting cache entries for tables that no longer exist...\n";
$tableArray = get_logtables($dbLink);

$sql = "SELECT DISTINCT tablename FROM ".CACHETABLENAME;
$result = perform_query($sql, $dbLink);
while($row = fetch_array($result)) {
	if(array_search($row['tablename'], $tableArray) === FALSE) {
		$sql = "DELETE FROM ".CACHETABLENAME." WHERE tablename='".$row['tablename']."'";
		perform_query($sql, $dbLink);
	}
}
// CDUKES 10-28-09: Uh...what's this for? It's deleting the cache for all tables except the merge table
// This is causing reloadcache to recalculate cache values for EVERY table except the merge on each run.
/*
   if($mergelog) {
   $sql = "DELETE FROM ".CACHETABLENAME." WHERE tablename!='".MERGELOGTABLE."'";
   perform_query($sql, $dbLink);
   }
 */

include ("lpdcache.php");
echo "Reloadcache Completed!\n";
flock($file, LOCK_UN);
?>
