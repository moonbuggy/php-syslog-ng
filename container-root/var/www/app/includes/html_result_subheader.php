<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
?>
<table>
<!-- cdukes - removed below to make things easier to read -->
<!-- http://code.google.com/p/php-syslog-ng/issues/detail?id=25 -->
<!--
<tr><td><center>
<i>Use this link to reference this query directly: </i>
<a href="<?php echo $_SERVER["PHP_SELF"]."?offset=".$offset.$ParamsGET; ?>">QUERY</a><br>
</center></td></tr></table>
-->
<table>
<tr><td>
<a href="index.php?pageId=searchform">BACK TO SEARCH</a><br>
<?php
/* cdukes - 2-28-08: Added !stristr($_SERVER[REQUEST_URI],"graph") below so that people didn't get confused by the results
   displayed on a Top 10 search (since below would show TOTAL results, not selected results, ie. Selected = 10 for Top 10)
   */
if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE && $num_results && !stristr($_SERVER[REQUEST_URI],"graph")) {
        echo "<i>Number of Entries Found</i>: <b>".commify($num_results)."</b>";
}
?>
</td><td>
<div class="sevlegend">
<!-- SEVERITY LEGEND<br>
<?php	/*<table>
	<tr>
	<td class="sev0">DEBUG</td>
	<td class="sev1">INFO</td>
	<td class="sev2">NOTICE</td>
	<td class="sev3">WARNING</td>
	<td class="sev4">ERROR</td>
	<td class="sev5">CRIT</td>
	<td class="sev6">ALERT</td>
	<td class="sev7">EMERG</td>
	</tr></table> */
?>
	<span class="sev0">DEBUG</span>
	<span class="sev1">INFO</span>
	<span class="sev2">NOTICE</span>
	<span class="sev3">WARNING</span>
	<span class="sev4">ERROR</span>
	<span class="sev5">CRIT</span>
	<span class="sev6">ALERT</span>
	<span class="sev7">EMERG</span>
	-->
<?php

if(!$priorityGET) {
	echo "<span class=\"sev0\"><a href=\"".$_SERVER["PHP_SELF"]."?priority[]=debug&".$priorityParamsGET."\">DEBUG</a></span>";
	echo "<span class=\"sev1\"><a href=\"".$_SERVER["PHP_SELF"]."?priority[]=info&".$priorityParamsGET."\">INFO</a></span>";
	echo "<span class=\"sev2\"><a href=\"".$_SERVER["PHP_SELF"]."?priority[]=notice&".$priorityParamsGET."\">NOTICE</a></span>";
	echo "<span class=\"sev3\"><a href=\"".$_SERVER["PHP_SELF"]."?priority[]=warning&".$priorityParamsGET."\">WARNING</a></span>";
	echo "<span class=\"sev4\"><a href=\"".$_SERVER["PHP_SELF"]."?priority[]=err&".$priorityParamsGET."\">ERROR</a></span>";
	echo "<span class=\"sev5\"><a href=\"".$_SERVER["PHP_SELF"]."?priority[]=crit&".$priorityParamsGET."\">CRIT</a></span>";
	echo "<span class=\"sev6\"><a href=\"".$_SERVER["PHP_SELF"]."?priority[]=alert&".$priorityParamsGET."\">ALERT</a></span>";
	echo "<span class=\"sev7\"><a href=\"".$_SERVER["PHP_SELF"]."?priority[]=emerg&".$priorityParamsGET."\">EMERG</a></span>";
} else {
	echo "<span><a href=\"".$_SERVER["PHP_SELF"]."?".$priorityParamsGET."\" class=aquery>ALL</a></span>";
	echo "<span class=\"sev0\"><a href=\"".$_SERVER["PHP_SELF"]."?";
	if (in_array('debug', $priority)) {
		$priority2=$priority;
		unset($priority2[array_search('debug',$priority2)]);
		if (count($priority2) > 0) { echo "priority[]=".implode("&priority[]=",$priority2)."&"; }
		echo $priorityParamsGET."\">DEBUG</a></span>";
	} else {
		echo "priority[]=debug&priority[]=".$priorityGET."&".$priorityParamsGET."\">DEBUG</a></span>";
	}
	echo "<span class=\"sev1\"><a href=\"".$_SERVER["PHP_SELF"]."?";
	if (in_array('info', $priority)) {
		$priority2=$priority;
		unset($priority2[array_search('info',$priority2)]);
		if (count($priority2) > 0) { echo "priority[]=".implode("&priority[]=",$priority2)."&"; }
		echo $priorityParamsGET."\">INFO</a></span>";
	} else {
		echo "priority[]=info&priority[]=".$priorityGET."&".$priorityParamsGET."\">INFO</a></span>";
	}
	echo "<span class=\"sev2\"><a href=\"".$_SERVER["PHP_SELF"]."?";
	if (in_array('notice', $priority)) {
		$priority2=$priority;
		unset($priority2[array_search('notice',$priority2)]);
		if (count($priority2) > 0) { echo "priority[]=".implode("&priority[]=",$priority2)."&"; }
		echo $priorityParamsGET."\">NOTICE</a></span>";
	} else {
		echo "priority[]=notice&priority[]=".$priorityGET."&".$priorityParamsGET."\">NOTICE</a></span>";
	}
	echo "<span class=\"sev3\"><a href=\"".$_SERVER["PHP_SELF"]."?";
	if (in_array('warning', $priority)) {
		$priority2=$priority;
		unset($priority2[array_search('warning',$priority2)]);
		if (count($priority2) > 0) { echo "priority[]=".implode("&priority[]=",$priority2)."&"; }
		echo $priorityParamsGET."\">WARNING</a></span>";
	} else {
		echo "priority[]=warning&priority[]=".$priorityGET."&".$priorityParamsGET."\">WARNING</a></span>";
	}
	echo "<span class=\"sev4\"><a href=\"".$_SERVER["PHP_SELF"]."?";
	if (in_array('err', $priority)) {
		$priority2=$priority;
		unset($priority2[array_search('err',$priority2)]);
		if (count($priority2) > 0) { echo "priority[]=".implode("&priority[]=",$priority2)."&"; }
		echo $priorityParamsGET."\">ERROR</a></span>";
	} else {
		echo "priority[]=err&priority[]=".$priorityGET."&".$priorityParamsGET."\">ERROR</a></span>";
	}
	echo "<span class=\"sev5\"><a href=\"".$_SERVER["PHP_SELF"]."?";
	if (in_array('crit', $priority)) {
		$priority2=$priority;
		unset($priority2[array_search('crit',$priority2)]);
		if (count($priority2) > 0) { echo "priority[]=".implode("&priority[]=",$priority2)."&"; }
		echo $priorityParamsGET."\">CRIT</a></span>";
	} else {
		echo "priority[]=crit&priority[]=".$priorityGET."&".$priorityParamsGET."\">CRIT</a></span>";
	}
	echo "<span class=\"sev6\"><a href=\"".$_SERVER["PHP_SELF"]."?";
	if (in_array('alert', $priority)) {
		$priority2=$priority;
		unset($priority2[array_search('alert',$priority2)]);
		if (count($priority2) > 0) { echo "priority[]=".implode("&priority[]=",$priority2)."&"; }
		echo $priorityParamsGET."\">ALERT</a></span>";
	} else {
		echo "priority[]=alert&priority[]=".$priorityGET."&".$priorityParamsGET."\">ALERT</a></span>";
	}
	echo "<span class=\"sev7\"><a href=\"".$_SERVER["PHP_SELF"]."?";
	if (in_array('emerg', $priority)) {
		$priority2=$priority;
		unset($priority2[array_search('emerg',$priority2)]);
		if (count($priority2) > 0) { echo "priority[]=".implode("&priority[]=",$priority2)."&"; }
		echo $priorityParamsGET."\">EMERG</a></span>";
	} else {
		echo "priority[]=emerg&priority[]=".$priorityGET."&".$priorityParamsGET."\">EMERG</a></span>";
	}
}

?>
</div>
</td></tr></table>
<!-- cdukes - removed below to make things easier to read -->
<!-- http://code.google.com/p/php-syslog-ng/issues/detail?id=25 -->
<?php
if(defined('DEBUG') && DEBUG == TRUE) {
   	?>
	   	<table class="query">
	   	<tr><td >
	   	The SQL query:<br>
	   	<?php echo $query; ?>
	   	<?php
} 
?>
</td></tr></table>
