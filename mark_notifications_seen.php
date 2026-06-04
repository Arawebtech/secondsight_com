<?php
// mark_notifications_seen.php
include('admin/include/db_config.php');

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Mark notifications as 'read'
    $query = "UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}
?>
