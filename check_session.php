<?php
session_start();
include('admin/include/db_config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_session_id'])) {
    echo json_encode(['status' => 'invalid', 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = $_SESSION['user_session_id'];

// Check if session ID matches the one in database
$query = "SELECT user_session_id FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Compare session IDs
    if ($user['user_session_id'] === $session_id) {
        echo json_encode(['status' => 'valid', 'message' => 'Session is valid']);
    } else {
        // Session ID doesn't match - user logged in from another device
        echo json_encode(['status' => 'invalid', 'message' => 'Session expired - logged in from another device']);
    }
} else {
    echo json_encode(['status' => 'invalid', 'message' => 'User not found']);
}

$stmt->close();
$conn->close();
?> 