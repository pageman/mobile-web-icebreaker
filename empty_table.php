<?php 

require_once("db.php");
$db = new DB;

$db->empty_tables();

print "Database table emptied";
