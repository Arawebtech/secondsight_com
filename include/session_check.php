<?php
// Include this file at the top of any page that requires authentication
if (!isset($_SESSION)) {
    session_start();
}

include('admin/include/db_config.php');
include('session_validator.php');

// Validate user session
if (!validateUserSession($conn)) {
    forceLogout('login.php');
}
?> 