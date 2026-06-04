<?php
session_start();
include('admin/include/db_config.php');

if(isset($_SESSION['user_id'])) {
    // Clear session ID from database
    $user_id = $_SESSION['user_id'];
    $clear_session = "UPDATE users SET user_session_id = NULL WHERE id = ?";
    $stmt = $conn->prepare($clear_session);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Clear all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    header("Location: index.php");
    exit;
} else {
    header("Location: login.php");
    exit;
}
?>
