<?php

$themes = array();
$theme_names = array();

// Espresso Tutti Colori
$themes[] = "Tutti Colori";
$tutti_colori["task divider"]["foreground"] = "blue";
$tutti_colori["task divider"]["background"] = "cyan";

$tutti_colori["id"]["foreground"] = null;
$tutti_colori["id"]["background"] = null;

$tutti_colori["title"]["foreground"] = "green";
$tutti_colori["title"]["background"] = null;

$tutti_colori["date"]["foreground"] = "green";
$tutti_colori["date"]["background"] = null;

$tutti_colori["header rule"]["foreground"] = "green";
$tutti_colori["header rule"]["background"] = null;

$tutti_colori["tags divider"]["foreground"] = "blue";
$tutti_colori["tags divider"]["background"] = "cyan";

$tutti_colori["toolbar divider"]["foreground"] = "blue";
$tutti_colori["toolbar divider"]["background"] = "cyan";

$tutti_colori["toolbar command"]["foreground"] = "green";
$tutti_colori["toolbar command"]["background"] = null;
$theme_names["Tutti Colori"] = $tutti_colori;

// Twilight
$themes[] = "Twilight";
$twilight["task divider"]["foreground"] = "blue";
$twilight["task divider"]["background"] = null;

$twilight["id"]["foreground"] = "blue";
$twilight["id"]["background"] = null;

$twilight["title"]["foreground"] = "green";
$twilight["title"]["background"] = null;

$twilight["date"]["foreground"] = "green";
$twilight["date"]["background"] = null;

$twilight["header rule"]["foreground"] = "blue";
$twilight["header rule"]["background"] = null;

$twilight["tags divider"]["foreground"] = "blue";
$twilight["tags divider"]["background"] = null;

$twilight["toolbar divider"]["foreground"] = "blue";
$twilight["toolbar divider"]["background"] = null;

$twilight["toolbar command"]["foreground"] = "green";
$twilight["toolbar command"]["background"] = null;
$theme_names["Twilight"] = $twilight;

// Basic
$themes[] = "Basic";
$basic["task divider"]["foreground"] = "blue";
$basic["task divider"]["background"] = null;

$basic["id"]["foreground"] = null;
$basic["id"]["background"] = null;

$basic["title"]["foreground"] = "cyan";
$basic["title"]["background"] = null;

$basic["date"]["foreground"] = "cyan";
$basic["date"]["background"] = null;

$basic["header rule"]["foreground"] = "blue";
$basic["header rule"]["background"] = null;

$basic["tags divider"]["foreground"] = "green";
$basic["tags divider"]["background"] = null;

$basic["toolbar divider"]["foreground"] = "green";
$basic["toolbar divider"]["background"] = null;

$basic["toolbar command"]["foreground"] = "cyan";
$basic["toolbar command"]["background"] = null;
$theme_names["Basic"] = $basic;

foreach (glob("themes/*.php") as $filename) {
    include $filename;
}

$db = simplexml_load_file(DATABASE);
$theme = $theme_names[$db->prefs->theme];