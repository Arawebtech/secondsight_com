<?php
// Session validation function
function validateUserSession($conn) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_session_id'])) {
        return false;
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
        $stmt->close();
        
        // Compare session IDs
        return ($user['user_session_id'] === $session_id);
    }
    
    $stmt->close();
    return false;
}

// Function to force logout and redirect
function forceLogout($redirect_url = 'login.php') {
    // Clear session ID from database if user is logged in
    if (isset($_SESSION['user_id'])) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        $clear_session = "UPDATE users SET user_session_id = NULL WHERE id = ?";
        $stmt = $conn->prepare($clear_session);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect
    header("Location: $redirect_url?session_expired=1");
    exit;
}
?> 