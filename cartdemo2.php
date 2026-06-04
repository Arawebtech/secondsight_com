<?php
session_start();
include('admin/include/db_config.php'); // Include database connection

// Get the form data
$email = $_POST['email'];
$name = $_POST['name'];
$mobile = $_POST['mobile'];
$password = $_POST['password'];

// Validate the data (check if email already exists)
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email already exists
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
} else {
    // Insert the new user into the database
    $insert_query = "INSERT INTO users (email, name, mobile, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssss", $email, $name, $mobile, $password);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully',
            'redirect' => 'checkout.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred while registering the user']);
    }
}

$stmt->close();
$conn->close();
?>
