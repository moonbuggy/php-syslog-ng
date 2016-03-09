<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com

require_once 'includes/html_header.php';

if($dbProblem) {
	echo "<b>A database connection problem was encountered.<br />Please check config/config.php to make sure everything is correct and make sure the MySQL server is up and running.</b>";
}
?>
<table class="pagecontent">
<tr><td><span class="longtext">
<h3 class="title">User Manual</h3>
<font color="blue">Click <a href="userguide.doc">here</a> to download the user guide.</font><br>
<h3 class="title">Outdated</h3>
Please go to <a href="http://nms.gdd.net/index.php/LogZilla_Installation_Guide">The install guide</a> for updated documentation on this project.
