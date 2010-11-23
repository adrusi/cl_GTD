#!/usr/bin/php
<?php
require_once("paths.php");
require_once("utils.php");
system("clear");
echo $colors->getColoredString($logo, "red");
sleep(1);
system("clear");
date_default_timezone_set('GMT');

require_once("class.php");

while ($GTD->continue != FALSE) {
	$GTD->main();
}
$GTD->close();