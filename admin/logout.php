<?php 

ob_start();
session_start();
date_default_timezone_set("Asia/Calcutta");
include('include/db_config.php');
global $dbLink;

$username=$_SESSION["name"];

session_destroy();
unset($_SESSION["name"]);

header("location: ../admin");

?>