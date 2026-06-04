<?php
session_start();
include('admin/include/db_config.php');

header('Content-Type: application/json');
$response = [
    'success' => false,
    'message' => 'An unknown error occurred.'
];

/*
IMPORTANT: Before this script can work, you need to create the 'user_notes' table.
You can use the following SQL query:

CREATE TABLE `user_notes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  `note_content` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_course_unique` (`user_id`, `course_id`)
);

*/

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $note_content = isset($_POST['note_content']) ? trim($_POST['note_content']) : '';

    if (empty($user_id) || empty($course_id)) {
        $response['message'] = 'Invalid data submitted.';
        echo json_encode($response);
        exit();
    }
    
    // Using INSERT ... ON DUPLICATE KEY UPDATE (UPSERT)
    $query = "INSERT INTO user_notes (user_id, course_id, note_content) VALUES (?, ?, ?)
              ON DUPLICATE KEY UPDATE note_content = ?";
    
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("iiss", $user_id, $course_id, $note_content, $note_content);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Note saved successfully!';
        } else {
            $response['message'] = 'Error saving note to the database.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Database error: Could not prepare statement.';
    }

    $conn->close();

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit();
?> 