<?php
session_start();
include('admin/include/db_config.php');

// Get the email from the request
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];

// Query to check if the email exists in the database
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email exists, fetch user data
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    echo json_encode(['exists' => true, 'user' => $user]);
} else {
    // Email doesn't exist
    echo json_encode(['exists' => false]);
}

$stmt->close();
$conn->close();
?>
