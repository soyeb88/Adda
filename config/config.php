<?php
session_start();
ob_start();
$timezone = date_default_timezone_set("America/New_York");

try{

	$con  = new PDO("mysql:dbname=FreeLand", "root", "Dhaka_22");
	//$con  = new PDO("mysql:dbname=CyQJdgEPMD;host=remotemysql.com:3306", "CyQJdgEPMD", "NVPMS8yBFc");

	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
}
catch(PDOException $e){

	echo "Connection failed: " . $e->getMessage(); 

}

?>