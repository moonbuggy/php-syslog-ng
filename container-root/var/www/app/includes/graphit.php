<?php
/*
 * graphit.php
 *
 * Developed by Clayton Dukes <cdukes@cisco.com>
 * Copyright (c) 2006 Cisco Systems, Inc.
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2006-07-11 - created
 *
 */

/* $Platon$ */

/* Modeline for ViM {{{
 * vim: set ts=4:
 * vim600: fdm=marker fdl=0 fdc=0:
 * }}} */

$basePath = dirname( __FILE__ );
require_once($basePath . "/jpgraph/jpgraph.php");
require_once($basePath . "/jpgraph/jpgraph_pie.php");
require_once($basePath . "/jpgraph/jpgraph_pie3d.php");
require_once($basePath . "/common_funcs.php");
require_once($basePath . "/html_header.php");

//========================================================================
// BEGIN: GET THE INPUT VARIABLES
//========================================================================
$host = get_input('host');
$host2 = get_input('host2');
$excludeHost = get_input('excludeHost');
$facility = get_input('facility');
$excludeFacility = get_input('excludeFacility');
$priority = get_input('priority');
$excludePriority = get_input('excludePriority');
$date = get_input('date');
$date2 = get_input('date2');
$time = get_input('time');
$time2 = get_input('time2');
$limit = get_input('limit');
$topx = get_input('topx');
$graphtype = get_input('graphtype');
$orderby = get_input('orderby');
// $orderby = "host";
$order = get_input('order');
$offset = get_input('offset');
if(!$offset) {
	$offset = 0;
}
$collapse = get_input('collapse');
$table = get_input('table');

// Set an arbitrary number of msg# and ExcludeMsg# vars
$msgvarnum=1;
$msgvarname="msg".$msgvarnum;
$excmsgvarname="ExcludeMsg".$msgvarnum;

while(get_input($msgvarname)) {
	${$msgvarname} = get_input($msgvarname);
	${$excmsgvarname} = get_input($excmsgvarname);

	$msgvarnum++;
	$msgvarname="msg".$msgvarnum;
	$excmsgvarname="ExcludeMsg".$msgvarnum;
}
//========================================================================
// END: GET THE INPUT VARIABLES
//========================================================================

/* BEGIN: Added by BPK to save search form variables into the session. */
$_SESSION['host'] = (isset($host)) ? $host : '';   
$_SESSION['host2'] = (isset($host2)) ? $host2 : '';
$_SESSION['excludeHost'] = (isset($excludeHost)) ? $excludeHost : '';
$_SESSION['regexpHost'] = (isset($regexpHost)) ? $regexpHost : '';
$_SESSION['program'] = (isset($program)) ? $program : '';   
$_SESSION['program2'] = (isset($program2)) ? $program2 : '';
$_SESSION['excludeProgram'] = (isset($excludeProgram)) ? $excludeProgram : '';
$_SESSION['regexpProgram'] = (isset($regexpProgram)) ? $regexpProgram : '';
$_SESSION['facility'] = (isset($facility)) ? $facility : '';
$_SESSION['excludeFacility'] = (isset($excludeFacility)) ? $excludeFacility : '';
$_SESSION['priority'] = (isset($priority)) ? $priority : '';
$_SESSION['excludePriority'] = (isset($excludePriority)) ? $excludePriority : '';
$_SESSION['date'] = (isset($date)) ? $date : '';   
$_SESSION['date2'] = (isset($date2)) ? $date2 : '';
$_SESSION['time'] = (isset($time)) ? $time : '';   
$_SESSION['time2'] = (isset($time2)) ? $time2 : '';
$_SESSION['limit'] = (isset($limit)) ? $limit : '';
$_SESSION['orderby'] = (isset($orderby)) ? $orderby : '';
$_SESSION['order'] = (isset($order)) ? $order : '';   
$_SESSION['offset'] = (isset($offset)) ? $offset : '';
$_SESSION['collapse'] = (isset($collapse)) ? $collapse : '';
$_SESSION['table'] = (isset($table)) ? $table : '';
$_SESSION['topx'] = (isset($topx)) ? $topx : '';
$_SESSION['graphtype'] = (isset($graphtype)) ? $graphtype : '';
for ($i=1; $i<=3; $i++) {
        $_SESSION['msg'.$i] = (isset(${'msg'.$i})?${'msg'.$i}:'');
        $_SESSION['ExcludeMsg'.$i] = (isset(${'ExcludeMsg'.$i})?${'ExcludeMsg'.$i}:'');
        $_SESSION['RegExpMsg'.$i] = (isset(${'RegExpMsg'.$i})?${'RegExpMsg'.$i}:'');
}
/* END: Added by BPK to save search form variables info the session. */

//========================================================================
// BEGIN: INPUT VALIDATION
//========================================================================
$inputValError = array();

if($excludeHost && !validate_input($excludeHost, 'excludeX')) {
	array_push($inputValError, "excludeHost");
}
if($host && !validate_input($host, 'host')) {
	array_push($inputValError, "host1");
}
if($host2 && !validate_input($host2, 'host')) {
	array_push($inputValError, "host2");
}
if($excludeFacility && !validate_input($excludeFacility, 'excludeX')) {
	array_push($inputValError, "excludeFacility");
}
if($facility && !validate_input($facility, 'facility')) {
	array_push($inputValError, "facility");
}
if($excludePriority && !validate_input($excludePriority, 'excludeX')) {
	array_push($inputValError, "excludePriority");
}
if($priority && !validate_input($priority, 'priority')) {
	array_push($inputValError, "priority");
}
if($time && !validate_input($time, 'time')) {
	array_push($inputValError, "time1");
}
if($time2 && !validate_input($time2, 'time')) {
	array_push($inputValError, "time2");
}
if($limit && !validate_input($limit, 'limit')) {
	array_push($inputValError, "limit");
}
if($topx && !validate_input($topx, 'topx')) {
	array_push($inputValError, "topx");
}
if($graphtype && !validate_input($graphtype, 'graphtype')) {
	array_push($inputValError, "graphtype");
}
if($orderby && !validate_input($orderby, 'orderby')) {
	array_push($inputValError, "orderby");
}
if($order && !validate_input($order, 'order')) {
	array_push($inputValError, "order");
}
if(!validate_input($offset, 'offset')) {
	array_push($inputValError, "offset");
}
if($collapse && !validate_input($collapse, 'collapse')) {
	array_push($inputValError, "collapse");
}
if($table && !validate_input($table, 'table')) {
	array_push($inputValError, "table");
}

if($inputValError) {
	require_once ($basePath . "/html_header.php");
	echo "Input validation error! The following fields had the wrong format:<p>";
	foreach($inputValError as $value) {
		echo $value."<br>";
	}
	require_once ($basePath . "/html_footer.php");
	exit;
}
//========================================================================
// END: INPUT VALIDATION
//========================================================================

//========================================================================
// BEGIN: BUILD AND EXECUTE SQL STATEMENT
// AND BUILD PARAMETER LIST FOR HTML GETS
//========================================================================
//------------------------------------------------------------------------
// Create WHERE statement and GET parameter list
//------------------------------------------------------------------------
$where = "";
$ParamsGET = "&";

if($table) {
	$ParamsGET=$ParamsGET."table=".$table."&";
}

if($limit) {
	$ParamsGET=$ParamsGET."limit=".$limit."&";
}

if($topx) {
	$ParamsGET=$ParamsGET."topx=".$topx."&";
}

if($graphtype) {
	$ParamsGET=$ParamsGET."graphtype=".$graphtype."&";
}

if($orderby) {
	$ParamsGET=$ParamsGET."orderby=".$orderby."&";
}

if($order) {
	$ParamsGET=$ParamsGET."order=".$order."&";
}

if($collapse) {
	$ParamsGET=$ParamsGET."collapse=".$collapse."&";
}

if($pageId) {
	$ParamsGET=$ParamsGET."pageId=".$pageId."&";
}

if($host2) {
	if ($where!="") {
		$where=$where." and ";
	}
	if($excludeHost==1) {
		$where = $where." host not like '%".$host2."%' ";
	}
	else {
		$where = $where." host like '%".$host2."%' ";
	}
	$ParamsGET=$ParamsGET."host2=".$host2."&excludeHost=".$excludeHost."&";
}

if($host) {
	$hostGET=implode("&host[]=",$host);
	$hostSQL=implode("','",$host);
	if($where!="") {
		$where = $where." and ";
	}
	if($excludeHost==1) {
		$where = $where." host not in ('".$hostSQL."') ";
	}
	else {
		$where = $where." host in ('".$hostSQL."') ";
	}
	$ParamsGET=$ParamsGET."host[]=".$hostGET."&excludeHost=".$excludeHost."&";	
}

if($facility) {
	$facilityGET=implode("&facility[]=",$facility);
	$facilitySQL=implode("','",$facility);
	if($where!="") {
		$where = $where." and ";
	}
	if($excludeFacility==1) {
		$where = $where." facility not in ('".$facilitySQL."') ";
	}
	else {
		$where = $where." facility in ('".$facilitySQL."') ";
	}
	$ParamsGET=$ParamsGET."facility[]=".$facilityGET."&excludeFacility=".$excludeFacility."&";
}

if($priority) {
	$priorityGET=implode("&priority[]=",$priority);
	$prioritySQL=implode("','",$priority);
	if($where!="") {
		$where = $where." and ";
	}
	if($excludePriority==1) {
		$where = $where." priority not in ('".$prioritySQL."') ";
	}
	else {
		$where = $where." priority in ('".$prioritySQL."') ";
	}
	$ParamsGET=$ParamsGET."priority[]=".$priorityGET."&excludePriority=".$excludePriority."&";
}

$fo = "";
$fo2 = "";

if($date) {
	$ParamsGET=$ParamsGET."date=".$date."&time=".$time."&";
	if(strcasecmp($date, 'now') == 0 || strcasecmp($date, 'today') == 0) {
		$date = date("Y-m-d");
	}
	elseif(strcasecmp($date, 'yesterday') == 0) {
		$date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
	}
	if(!$time) {
		$time = "00:00:00";
	}
	elseif(strcasecmp($time, 'now') == 0) {
		$time = date("H:i:s");
	}
	$fo = $date." ".$time ;
}
if($date2) {
	$ParamsGET=$ParamsGET."date2=".$date2."&time2=".$time2."&";
	if(strcasecmp($date2, 'now') == 0 || strcasecmp($date2, 'today') == 0) {
		$date2 = date("Y-m-d");
	}
	elseif(strcasecmp($date2, 'yesterday') == 0) {
		$date2 = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
	}
	if(!$time2) {
		$time2 = "23:59:59";
	}
	elseif(strcasecmp($time2, 'now') == 0) {
		$time2 = date("H:i:s");
	}
	$fo2 = $date2." ".$time2 ;
}

if($fo && $fo2) {
	if($where != "") {
		$where = $where." and ";
	}
	$where = $where." fo between '".$fo."' and '".$fo2."' ";
}
elseif($fo) {
	if($where != "") {
		$where = $where." and ";
	}
	$where = $where." fo > '".$fo."' ";
}
elseif($fo2) {
	if($where != "") {
		$where = $where." and ";
	}
	$where = $where." fo < '".$fo2."' ";
}

$msgvarnum=1;
$msgvarname="msg".$msgvarnum;
$excmsgvarname="ExcludeMsg".$msgvarnum;

while(isset(${$msgvarname})) {
	if($where !="") {
		$where = $where." and ";
	}
	if(${$excmsgvarname} == "on") {
		$where = $where." msg not like '%".${$msgvarname}."%' ";
		$ParamsGET=$ParamsGET.$excmsgvarname."=".${$excmsgvarname}."&";
	}
	else {
		$where = $where." msg like '%".${$msgvarname}."%' ";
	}
	$ParamsGET=$ParamsGET.$msgvarname."=".${$msgvarname}."&";
	$msgvarnum++;
	$msgvarname="msg".$msgvarnum;
	$excmsgvarname="ExcludeMsg".$msgvarnum;
}

//------------------------------------------------------------------------
// Create the GET string without host variables
//------------------------------------------------------------------------
$pieces = explode("&", $ParamsGET);
$hostParamsGET = "";
foreach($pieces as $value) {
	if(!strstr($value, "host[]=") && !strstr($value, 'excludeHost=') && !strstr($value, 'offset=') && $value) {
		$hostParamsGET = $hostParamsGET.$value."&";
	}
}

//------------------------------------------------------------------------
// Create the complete SQL statement
// SQL_CALC_FOUND_ROWS is a MySQL 4.0 feature that allows you to get the
// total number of results if you had not used a LIMIT statement. Using
// it saves an extra query to get the total number of rows.
//------------------------------------------------------------------------
if($table) {
	$srcTable = $table;
}
else {
	$srcTable = DEFAULTLOGTABLE;
}
// CDUKES: May 07, 2009: Added for different graph types
switch ($graphtype) {
	case "topmsgs":
		   	if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
			   	$query = "SELECT SQL_CALC_FOUND_ROWS id, host, msg, SUM(counter) as count FROM ".$srcTable." ";
		   	}
		   	else {
			   	$query = "SELECT id, host, msg, SUM(counter) as count FROM ".$srcTable." ";
		   	}
   	if($where) {
	   	$query = $query."WHERE ".$where." GROUP by msg ORDER BY count $order LIMIT " .$topx;
   	}
   	else {
	   	$query = $query."GROUP by msg ORDER BY count $order LIMIT " .$topx;
   	}
   	break;
	case "prog":
		   	if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
			   	$query = "SELECT SQL_CALC_FOUND_ROWS id, host, program, SUM(counter) as count FROM ".$srcTable." ";
		   	}
		   	else {
			   	$query = "SELECT id, host, program, SUM(counter) as count FROM ".$srcTable." ";
		   	}
   	if($where) {
	   	$query = $query."WHERE ".$where." AND program RLIKE '^[a-zA-Z]+[a-zA-Z0-9/()._\-]+$' GROUP by program ORDER BY count $order LIMIT " .$topx;
   	}
   	else {
	   	$query = $query."WHERE program RLIKE '^[a-zA-Z]+[a-zA-Z0-9/()._\-]+$' GROUP by program ORDER BY count $order LIMIT " .$topx;
   	}
   	break;
	case "fac":
		   	if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
			   	$query = "SELECT SQL_CALC_FOUND_ROWS id, host, facility, SUM(counter) as count FROM ".$srcTable." ";
		   	}
		   	else {
			   	$query = "SELECT id, host, facility, SUM(counter) as count FROM ".$srcTable." ";
		   	}
   	if($where) {
	   	$query = $query."WHERE ".$where." GROUP by facility ORDER BY count $order LIMIT " .$topx;
   	}
   	else {
	   	$query = $query."GROUP by facility ORDER BY count $order LIMIT " .$topx;
   	}
   	break;
	case "pri":
		   	if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
			   	$query = "SELECT SQL_CALC_FOUND_ROWS id, host,msg,priority, SUM(counter) as count FROM ".$srcTable." ";
		   	}
		   	else {
			   	$query = "SELECT id, host,msg,priority, SUM(counter) as count FROM ".$srcTable." ";
		   	}
   	if($where) {
	   	$query = $query."WHERE ".$where." GROUP by priority ORDER BY count $order LIMIT " .$topx;
   	}
   	else {
	   	$query = $query."GROUP by priority ORDER BY count $order LIMIT " .$topx;
   	}
   	break;
   	default: // Top Hosts
   	// CDUKES: Jun 18, 2008: Added in support of the SQZ feature
   	// CDUKES: Feb 23, 2009: Changed DEFAULTLOGTABLE to srcTable
	// CDUKES: Jun 05, 2009: Removed if SQZ statement since SUM(counter) yeilds the same result as count(*) in non-squeeze enabled environments, This is because the COUNTER column defaults to 1 when an entry is inserted.
   	// Ref: http://groups.google.com/group/php-syslog-ng-dev/browse_thread/thread/e3bddab850f2ea0a/e834f8d983a7f339?lnk=gst&q=dfound#e834f8d983a7f339
   	if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
	   	$query = "SELECT SQL_CALC_FOUND_ROWS id, host, SUM(counter) as count FROM ".$srcTable." ";
   	}
   	else {
	   	$query = "SELECT id, host, SUM(counter) as count FROM ".$srcTable." ";
   	}
   	if($where) {
	   	$query = $query."WHERE ".$where." GROUP by host ORDER BY count $order LIMIT " .$topx;
   	}
   	else {
	   	$query = $query."GROUP by host ORDER BY count $order LIMIT " .$topx;
   	}
}

   //   die($query);
//------------------------------------------------------------------------
// Execute the query
// The FOUND_ROWS function returns the value from the SQL_CALC_FOUND_ROWS
// count.
//------------------------------------------------------------------------
 $results = perform_query($query, $dbLink);
if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
	$num_results_array = perform_query("SELECT FOUND_ROWS()", $dbLink);
	$num_results_array = fetch_array($num_results_array);
	$num_results = $num_results_array[0];
}

//========================================================================
// END: BUILD AND EXECUTE SQL STATEMENT
// AND BUILD PARAMETER LIST FOR HTML GETS
//========================================================================

//========================================================================
// BEGIN: BUILDING THE HTML PAGE
//========================================================================
// Print result sub-header
require_once 'includes/html_result_subheader.php';
// If there is a result list then print it
// if (count($result_array)){

	//------------------------------------------------------------------------
	// If the query returned some results then start the table with the
	// results
	//------------------------------------------------------------------------

	?>
	<FORM name="export" method="POST" action= "includes/excel.php" name="checkboxes[]">
	   	<table width=100%>
	   	<tr class="resultsheader" bgcolor="<?php echo HEADER_COLOR?>">
	   	<div align="left">
	   	<td>
		<input type="submit" value="Export Found Rows to: ">
		<select name="rpt_type">
		<option selected value="xls">XLS</option>
		<option value="xml">XLSX</option>
		<option value="csv">CSV</option>
		<!-- Disabled PDF for now because it's not formatting properly
		<option value="pdf">PDF</option>
		-->
		</select>
		<input type="hidden" name="table" value="<?php echo $srcTable?>">
		</td>
		</div>
		<table align="center">
		<tr><td>
		<?php
		// Set up Graph variables
		$slice = 0;
	$type = "pie3d";
	$mapName = 'Top10'; 
	$fileName = IMG_CACHE_DIR . "Top10-" . time(3600) . ".png";

	if ( $slice > 0 ) {
		// include ("pageload.php");
		$url = SITEURL . "$ParamsGET";
		$mode = "JS";
		// die ("$url -- $mode -- $host");
		g_redirect($url,$mode);
		die ("Error in redirect to graph"); // just in case g_redir fails or something weird
	}


$result = perform_query($query, $dbLink) or die (mysql_error());
$numrows = mysql_num_rows($result);
// echo "$numrows Rows\n<br>";

if ( $numrows < 1 ) { 
	die ("<center>No results found.<br><a href=\"index.php?pageId=searchform\">BACK TO SEARCH</a></center>");
} else {
   	while ($row = mysql_fetch_assoc($result)) {
	   	$counts[]  = $row['count'];
	   	// Use something like below to filter off domain names
	   	// $host[]   = preg_replace("/\.tld.domain.*/", "", $row['host']);
	   	$hosts[]   = $row['host'];
	   	$msgs[]   = $row['msg'];
	   	$ids[]   = $row['id'];
	   	// added below to strip fake event data from dbgen.pl
		$row['msg'] = str_replace('DBGen:', '', $row['msg']);
	   	if (CISCO_TAG_PARSE) {
		   	$row['msg'] = preg_replace('/\s:/', ':', $row['msg']);
		   	$s_msgs[] = substr(preg_replace('/.*%(.*?):.*/', '$1', $row['msg']),0,30);
	   	} else {
		// added below to grab just a substring of the messages so that our key doesn't fill the whole chart
		$s_msgs[] = substr($row['msg'],0,30);
		}
	   	$pris[]   = $row['priority'];
	   	$progs[]   = $row['program'];
	   	$facs[]   = $row['facility'];
   	}
}
?>
<?php
for ($i=0; $i<count($ids); $i++) {
   	echo "<input type=\"hidden\" name=\"dbid[]\" value=\"".$ids[$i]."\">\n";
}
?>
<input type="hidden" name="table" value="<?php echo $srcTable?>">
</FORM>
<?php
// CDUKES: Jun 18, 2008: Added in support of the SQZ feature
// CDUKES: Jun 05, 2009: Removed if SQZ statement since SUM(counter) yeilds the same result as count(*) in non-squeeze enabled environments, This is because the COUNTER column defaults to 1 when an entry is inserted.
   	// Get Total number of rows
   	$query="SELECT SUM(counter) from " . $srcTable;
   	$result = perform_query($query, $dbLink) or die (mysql_error());
   	$numrows = fetch_array($result);
   	$totalrows = commify($numrows[0]);

// A new pie graph
$graph = new PieGraph(640,480,'auto');
$graph->SetShadow();

// Title setup
/* cdukes - 2-28-08: Added a test to notify the user if they selected more TopX than what was available in the database
Example: Selecting Top 100 when only 50 hosts are in the DB
 */
   	$numhosts = (count($hosts)); 
 // die("Hostcount:$numhosts \nTopx: $topx\n");
switch ($order) {
   	case "ASC":
	   	$top = "Bottom";
   	break;
   	default:
   	$top = "Top";
}

switch ($graphtype) {
   	case "topmsgs":
	   	if ($numhosts >= $topx) {
		   	$graph->title->Set("$top $topx Messages of " . $totalrows . " total");
	   	} else {
		   	$graph->title->Set("$top $numhosts Messages of " . $totalrows . " total\n(Unable to get $top $topx, You only have $numhosts host that meets the search criteria)");
	   	}
   	break;
   	case "pri":
		   	$graph->title->Set("$top $numhosts Priorities of " . $totalrows . " total messages");
   	break;
   	case "prog":
		   	$graph->title->Set("$top $numhosts Programs of " . $totalrows . " total messages");
   	break;
   	case "fac":
		   	$graph->title->Set("$top $numhosts Facilities of " . $totalrows . " total messages");
   	break;
   	default:
   	if ($numhosts >= $topx) {
	   	$graph->title->Set("$top $topx Hosts of " . $totalrows . " messages");
   	} else {
	   	$graph->title->Set("$top $numhosts Hosts of " . $totalrows . " messages\n(Unable to get $top $topx, You only have $numhosts host that meets the search criteria)");
   	}
}
$topx = $numhosts;
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Setup the pie plot
   	$p1 = new PiePlot3D($counts);
switch ($graphtype) {
   	case "topmsgs":
   	$p1->SetLegends($s_msgs);
	break;
   	case "prog":
   	$p1->SetLegends($progs);
	break;
   	case "fac":
   	$p1->SetLegends($facs);
	break;
   	case "pri":
   	$p1->SetLegends($pris);
	break;
	default: //hosts
   	$p1->SetLegends($hosts);
}


$targ = array();
//count number of hosts for pie slices
for($y=0; $y<$numhosts; $y++) {
switch ($graphtype) {
   	case "topmsgs":
	   	array_push($targ, $_SERVER["PHP_SELF"] . "?table=$table&excludeHost=0&host2=&excludeProgram=1&program2=&excludeFacility=1&excludePriority=1&date$date=&time=&date2=$date2&time2=&limit=$limit&topx=$topx&orderby=$orderby&order=$order&msg1=".urlencode($msgs[$y])."&msg2=&msg3=&pageId=Search");
	break;
   	case "prog":
	   	array_push($targ, $_SERVER["PHP_SELF"] . "?table=logs&excludeHost=0&host2=&host[]=$hosts[$y]&excludeProgram=0&program2=&program[]=$progs[$y]&excludeFacility=1&excludePriority=1&date=&time=&date2=&time2=&limit=$limit&topx=$topx&orderby=$orderby&order=$order&graphtype=tophosts&msg1=&msg2=&msg3=&pageId=Search");
		break;
   	case "fac":
	   	array_push($targ, $_SERVER["PHP_SELF"] . "?table=logs&excludeHost=0&host2=&host[]=$hosts[$y]&excludeProgram=0&program2=&excludeFacility=0&facility[]=$facs[$y]&excludePriority=1&date=&time=&date2=&time2=&limit=$limit&topx=$topx&orderby=$orderby&order=$order&graphtype=tophosts&msg1=&msg2=&msg3=&pageId=Search");
		break;
   	case "pri":
	   	array_push($targ, $_SERVER["PHP_SELF"] . "?table=$table&excludeHost=0&host2=&host[]=$hosts[$y]&excludeProgram=1&program2=&excludeFacility=1&excludePriority=0&priority[]=$pris[$y]&date$date=&time=&date2=$date2&time2=&limit=$limit&topx=$topx&orderby=$orderby&order=$order&msg1=&msg2=&msg3=&pageId=Search");
		break;
	default: //hosts
	   	array_push($targ, $_SERVER["PHP_SELF"] . "?table=$table&excludeHost=0&host2=&host[]=$hosts[$y]&excludeProgram=1&program2=&excludeFacility=1&excludePriority=1&date$date=&time=&date2=$date2&time2=&limit=$limit&topx=$topx&orderby=$orderby&order=$order&msg1=&msg2=&msg3=&pageId=Search");
   	}
}
  // die(print_r($targ));

$p1->SetCSIMTargets($targ,$alts);

// Horizontal: 'left','right','center'
// Vertical: 'bottom','top','center' 
$graph->legend->SetAbsPos(10,20,'right','top');
// $graph->legend->Pos(0.5,0.5); 
// $graph->legend->SetColumns(2); 
$graph->legend->SetFont(FF_VERDANA,FS_NORMAL, 8);


// Adjust size and position of plot
$p1->SetSize(0.40);
$p1->SetCenter(0.39,0.6);

// Setup slice labels and move them into the plot
$p1->value->SetFont(FF_FONT1,FS_BOLD);
$p1->value->SetColor("darkred");
$p1->SetLabelPos(0.70);

// Set perM  > 1  below to enable per million labels
$perM = 0;
if ( $perM < 1 ) {
   	$p1->SetLabelType(PIE_VALUE_ABS);
   	$p1->value->SetFormat("%d");
} else {
   	$p1->SetLabelType(PIE_VALUE_PER); 
	$p1->value->SetFormat(".%dM");
}
// Set percentage to enable per percent labels
$percentage = 1;
if ( $percentage > 0 ) {
   	$p1->SetLabelType(PIE_VALUE_ABS);
   	$p1->value->SetFormat("%d%%");
   	$p1->SetValueType(PIE_VALUE_PERCENTAGE);
}


// Set theme colors
// Options are "earth", "sand", "water" and doodoo, no, I mean "pastel" :-)
$p1->SetTheme("earth");

// Explode all slices
$p1->ExplodeAll($topx);

// Add drop shadow
$aColor = "darkgray";
$p1->SetShadow($aColor);

// Finally add the plot
$graph->Add($p1);

// ... and stroke it
// $graph->Stroke();
//$ih = $graph->Stroke(_IMG_HANDLE); 
// $graph->StrokeCSIM("$graph_name");

$graph->Stroke($fileName);

// $mapName = 'Top10'; 
$imgMap = $graph->GetHTMLImageMap($mapName);
// die("?offset=".$offset. "PPP" .$ParamsGET);
echo "$imgMap <TD ALIGN=\"center\"><img src=\"$fileName\" alt=\"$mapName Graph - Click on slice to drill down\" ismap usemap=\"#$mapName\" border=\"0\"></TD></TR>";
require_once 'includes/html_footer.php';

//------------------------------------------------------------------------
// Else just direct the user back to the form
//------------------------------------------------------------------------
/* } else {
   echo "No results found.<br><a href=\"index.php?pageId=searchform\">BACK TO SEARCH</a>";
   }
 */

//========================================================================
// END: BUILDING THE HTML PAGE
//========================================================================
?>
