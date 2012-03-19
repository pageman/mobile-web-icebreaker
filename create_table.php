<?php 

require_once("db.php");
$db = new DB;

$db->install_tables();

print "Database table created";
