<?php

$servername = "localhost";
$username = "jhbewdmy_ssf";
$password = "NDyakq.q#o_r";
$dbname = "jhbewdmy_ssf";

// Defining base url
if (!defined('BASE_URL')) {
    define("BASE_URL", "https://www.secondsightfoundation.com/");
}

// Getting Admin url
if (!defined('ADMIN_URL')) {
    define("ADMIN_URL", BASE_URL . "admin" . "/");
}

$conn = mysqli_connect($servername, $username, $password, $dbname);
mysqli_set_charset($conn, "utf8mb4");
$conn->set_charset("utf8mb4");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Calcutta');

$base_url = BASE_URL;

?>