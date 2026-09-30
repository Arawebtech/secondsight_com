<?php

$servername = "localhost";
$username = "secondsight_com_user";
$password = "Solutions@321@";
$dbname = "secondsight_com_db";

// $username = "root";
// $password = "";
// $dbname = "secondsight_com_db";

// Defining base url
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    $isLocalhost = ($domainName == 'localhost' || $domainName == '127.0.0.1');
    if ($isLocalhost) {
        define("BASE_URL", $protocol . $domainName . "/araweb/secondsight_com_backup/");
    } else {
        define("BASE_URL", $protocol . $domainName . "/");
    }
    define("ADMIN_URL", BASE_URL . "admin" . "/");
}

$base_url = BASE_URL;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>